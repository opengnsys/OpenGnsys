#include "schedule.h"
#include "list.h"
#include <sys/types.h>
#include <stdbool.h>
#include <stdint.h>
#include <stdlib.h>
#include <syslog.h>
#include <time.h>
#include <ev.h>

struct og_schedule *current_schedule = NULL;
static LIST_HEAD(schedule_list);

static void og_schedule_add(struct og_schedule *new)
{
	struct og_schedule *schedule, *next;

	list_for_each_entry_safe(schedule, next, &schedule_list, list) {
		if (new->seconds < schedule->seconds) {
			list_add_tail(&new->list, &schedule->list);
			return;
		}
	}
	list_add_tail(&new->list, &schedule_list);
}

/* Returns the days in a month from the weekday. */
static void get_days_from_weekday(struct tm *tm, int wday, int *days, int *j)
{
	int i, mday = 0;

	tm->tm_mday = 1;

	//Shift week to start on Sunday instead of Monday
	if (wday == 6)
		wday = 0;
	else
		wday++;

	/* A bit bruteforce, but simple. */
	for (i = 0; i <= 30; i++) {
		mktime(tm);
		/* Not this weekday, skip. */
		if (tm->tm_wday != wday) {
			tm->tm_mday++;
			continue;
		}
		/* Not interested in next month. */
		if (tm->tm_mday < mday)
			break;

		/* Found a matching. */
		mday = tm->tm_mday;
		days[(*j)++] = tm->tm_mday;
		tm->tm_mday++;
	}
}

/* Returns the days in the given week. */
static void get_days_from_week(struct tm *tm, int week, int *days, int *k)
{
	int i, j, week_counter = 0;
	bool week_over = false;

	tm->tm_mday = 1;

	/* Remaining days of this month. */
	for (i = 0; i <= 30; i++) {
		mktime(tm);

		/* Last day of this week? */
		if (tm->tm_wday == 6)
			week_over = true;

		/* Not the week we are searching for. */
		if (week != week_counter) {
			tm->tm_mday++;
			if (week_over) {
				week_counter++;
				week_over = false;
			}
			continue;
		}

		/* Found matching. */
		for (j = tm->tm_wday; j <= 6; j++) {
			days[(*k)++] = tm->tm_mday++;
			mktime(tm);
		}
		break;
	}
}

static int monthdays[12] = { 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 };

static int last_month_day(struct tm *tm)
{
	/* Leap year? Adjust it. */
	if (tm->tm_mon == 1) {
		tm->tm_mday = 29;
		mktime(tm);
		if (tm->tm_mday == 29)
			return 29;

		tm->tm_mon = 1;
	}

	return monthdays[tm->tm_mon];
}

/* Returns the days in the given week. */
static void get_last_week(struct tm *tm, int *days, int *j)
{
	int i, last_day;

	last_day = last_month_day(tm);
	tm->tm_mday = last_day;

	for (i = last_day; i >= last_day - 6; i--) {
		mktime(tm);

		days[(*j)++] = tm->tm_mday;


		/* Last day of this week? */
		if (tm->tm_wday == 1)
			break;

		tm->tm_mday--;
	}
}

static void og_parse_years(uint16_t years_mask, int years[])
{
	int i, j = 0;

	for (i = 0; i < 16; i++) {
		if ((1 << i) & years_mask)
			years[j++] = 2010 + i - 1900;
	}
}

static void og_parse_months(uint16_t months_mask, int months[])
{
	int i, j = 0;

	for (i = 0; i < 12; i++) {
		if ((1 << i) & months_mask)
			months[j++] = i + 1;
	}
}

static void og_parse_days(uint32_t days_mask, int *days)
{
	int i, j = 0;

	for (i = 0; i < 31; i++) {
		if ((1 << i) & days_mask)
			days[j++] = i + 1;
	}
}

static void og_parse_hours(uint16_t hours_mask, uint8_t am_pm, int hours[])
{
	int pm = 12 * am_pm;
	int i, j = 0;

	for (i = 0; i < 12; i++) {
		if ((1 << i) & hours_mask)
			hours[j++] = i + pm + 1;
	}
}

static void og_schedule_remove_duplicates()
{
	struct og_schedule *schedule, *next, *prev = NULL;

	list_for_each_entry_safe(schedule, next, &schedule_list, list) {
		if (!prev) {
			prev = schedule;
			continue;
		}
		if (prev->seconds == schedule->seconds &&
		    prev->task_id == schedule->task_id) {
			list_del(&prev->list);
			free(prev);
		}
		prev = schedule;
	}
}

static bool og_schedule_stale(time_t seconds)
{
	time_t now;

	now = time(NULL);
	if (seconds < now)
		return true;

	return false;
}

static void og_schedule_create_weekdays(int month, int year,
					int *hours, int minutes, int week_days,
					uint32_t task_id, uint32_t schedule_id,
					enum og_schedule_type type,
					bool on_start)
{
	struct og_schedule *schedule;
	int month_days[5];
	int n_month_days;
	time_t seconds;
	uint32_t wday;
	struct tm tm;
	int k, l;

	for (wday = 0; wday < 7; wday++) {
		if (!((1 << wday) & week_days))
			continue;

		memset(&tm, 0, sizeof(tm));
		tm.tm_mon = month;
		tm.tm_year = year;

		n_month_days = 0;
		memset(month_days, 0, sizeof(month_days));
		get_days_from_weekday(&tm, wday, month_days, &n_month_days);

		for (k = 0; month_days[k] != 0 && k < n_month_days; k++) {
			for (l = 0; hours[l] != 0 && l < 31; l++) {
				memset(&tm, 0, sizeof(tm));
				tm.tm_year = year;
				tm.tm_mon = month;
				tm.tm_mday = month_days[k];
				tm.tm_hour = hours[l] - 1;
				tm.tm_min = minutes;
				seconds = mktime(&tm);

				if (on_start && og_schedule_stale(seconds))
					continue;

				schedule = (struct og_schedule *)
					calloc(1, sizeof(struct og_schedule));
				if (!schedule)
					return;

				schedule->seconds = seconds;
				schedule->task_id = task_id;
				schedule->schedule_id = schedule_id;
				schedule->type = type;
				og_schedule_add(schedule);
			}
		}
	}
}

static void og_schedule_create_weeks(int month, int year,
				     int *hours, int minutes, int weeks,
				     uint32_t task_id, uint32_t schedule_id,
				     enum og_schedule_type type, bool on_start)
{
	struct og_schedule *schedule;
	int month_days[7];
	int n_month_days;
	time_t seconds;
	struct tm tm;
	int week;
	int k, l;

	for (week = 0; week < 5; week++) {
		if (!((1 << week) & weeks))
			continue;

		memset(&tm, 0, sizeof(tm));
		tm.tm_mon = month;
		tm.tm_year = year;

		n_month_days = 0;
		memset(month_days, 0, sizeof(month_days));
		if (week == 5)
			get_last_week(&tm, month_days, &n_month_days);
		else
			get_days_from_week(&tm,  week, month_days, &n_month_days);

		for (k = 0; month_days[k] != 0 && k < n_month_days; k++) {
			for (l = 0; hours[l] != 0 && l < 31; l++) {
				memset(&tm, 0, sizeof(tm));
				tm.tm_year = year;
				tm.tm_mon = month;
				tm.tm_mday = month_days[k];
				tm.tm_hour = hours[l] - 1;
				tm.tm_min = minutes;
				seconds = mktime(&tm);

				if (on_start && og_schedule_stale(seconds))
					continue;

				schedule = (struct og_schedule *)
					calloc(1, sizeof(struct og_schedule));
				if (!schedule)
					return;

				schedule->seconds = seconds;
				schedule->task_id = task_id;
				schedule->schedule_id = schedule_id;
				schedule->type = type;
				og_schedule_add(schedule);
			}
		}
	}
}

static void og_schedule_create_days(int month, int year,
				    int *hours, int minutes, int *days,
				    uint32_t task_id, uint32_t schedule_id,
				    enum og_schedule_type type, bool on_start)
{
	struct og_schedule *schedule;
	time_t seconds;
	struct tm tm;
	int k, l;

	for (k = 0; days[k] != 0 && k < 31; k++) {
		for (l = 0; hours[l] != 0 && l < 31; l++) {

			memset(&tm, 0, sizeof(tm));
			tm.tm_year = year;
			tm.tm_mon = month;
			tm.tm_mday = days[k];
			tm.tm_hour = hours[l] - 1;
			tm.tm_min = minutes;
			seconds = mktime(&tm);

			if (on_start && og_schedule_stale(seconds))
				continue;

			schedule = (struct og_schedule *)
				calloc(1, sizeof(struct og_schedule));
			if (!schedule)
				return;

			schedule->seconds = seconds;
			schedule->task_id = task_id;
			schedule->schedule_id = schedule_id;
			schedule->type = type;
			og_schedule_add(schedule);
		}
	}
}

void og_schedule_create(unsigned int schedule_id, unsigned int task_id,
			enum og_schedule_type type,
			struct og_schedule_time *time)
{
	int year, month, minutes;
	int months[12] = {};
	int years[12] = {};
	int hours[12] = {};
	int days[31] = {};
	int i, j;

	og_parse_years(time->years, years);
	og_parse_months(time->months, months);
	og_parse_days(time->days, days);
	og_parse_hours(time->hours, time->am_pm, hours);
	minutes = time->minutes;

	for (i = 0; years[i] != 0 && i < 12; i++) {
		for (j = 0; months[j] != 0 && j < 12; j++) {
			month = months[j] - 1;
			year = years[i];

			if (time->week_days)
				og_schedule_create_weekdays(month, year,
							    hours, minutes,
							    time->week_days,
							    task_id,
							    schedule_id,
							    type,
							    time->on_start);

			if (time->weeks)
				og_schedule_create_weeks(month, year,
							 hours, minutes,
							 time->weeks,
							 task_id,
							 schedule_id,
							 type, time->on_start);

			if (time->days)
				og_schedule_create_days(month, year,
							hours, minutes,
							days,
							task_id,
							schedule_id,
							type, time->on_start);
		}
	}

	og_schedule_remove_duplicates();
}

void og_schedule_delete(struct ev_loop *loop, uint32_t schedule_id)
{
	struct og_schedule *schedule, *next;

	list_for_each_entry_safe(schedule, next, &schedule_list, list) {
		if (schedule->schedule_id != schedule_id)
			continue;

		list_del(&schedule->list);
		if (current_schedule == schedule) {
			ev_timer_stop(loop, &schedule->timer);
			current_schedule = NULL;
			og_schedule_refresh(loop);
		}
		free(schedule);
	}
}

void og_schedule_update(struct ev_loop *loop, unsigned int schedule_id,
			unsigned int task_id, struct og_schedule_time *time)
{
	og_schedule_delete(loop, schedule_id);
	og_schedule_create(schedule_id, task_id, OG_SCHEDULE_TASK, time);
}

static void og_agent_timer_cb(struct ev_loop *loop, ev_timer *timer, int events)
{
	struct og_schedule *current;

	current = container_of(timer, struct og_schedule, timer);
	og_schedule_run(current->task_id, current->schedule_id, current->type);

	ev_timer_stop(loop, timer);
	list_del(&current->list);
	free(current);

	og_schedule_next(loop);
}

void og_schedule_next(struct ev_loop *loop)
{
	struct og_schedule *schedule;
	time_t now, seconds;

	if (list_empty(&schedule_list)) {
		current_schedule = NULL;
		return;
	}

	schedule = list_first_entry(&schedule_list, struct og_schedule, list);
	now = time(NULL);
	if (schedule->seconds <= now)
		seconds = 0;
	else
		seconds = schedule->seconds - now;

	ev_timer_init(&schedule->timer, og_agent_timer_cb, seconds, 0.);
	ev_timer_start(loop, &schedule->timer);
	current_schedule = schedule;
}

void og_schedule_refresh(struct ev_loop *loop)
{
	if (current_schedule)
		ev_timer_stop(loop, &current_schedule->timer);

	og_schedule_next(loop);
}
