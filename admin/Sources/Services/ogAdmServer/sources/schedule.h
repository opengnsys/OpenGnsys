#ifndef _OG_SCHEDULE_H_
#define _OG_SCHEDULE_H_

#include <stdint.h>
#include <stdbool.h>
#include "dbi.h"
#include "list.h"
#include <ev.h>

struct og_schedule_time {
	uint32_t	years;
	uint32_t	months;
	uint32_t	weeks;
	uint32_t	week_days;
	uint32_t	days;
	uint32_t	hours;
	uint32_t	am_pm;
	uint32_t	minutes;
	bool		on_start;
};

enum og_schedule_type {
	OG_SCHEDULE_TASK,
	OG_SCHEDULE_COMMAND,
};

struct og_schedule {
	struct list_head	list;
	struct ev_timer		timer;
	time_t			seconds;
	unsigned int		task_id;
	unsigned int		schedule_id;
	enum og_schedule_type	type;
};

void og_schedule_create(unsigned int schedule_id, unsigned int task_id,
			enum og_schedule_type type,
			struct og_schedule_time *time);
void og_schedule_update(struct ev_loop *loop, unsigned int schedule_id,
			unsigned int task_id, struct og_schedule_time *time);
void og_schedule_delete(struct ev_loop *loop, uint32_t schedule_id);
void og_schedule_next(struct ev_loop *loop);
void og_schedule_refresh(struct ev_loop *loop);
void og_schedule_run(unsigned int task_id, unsigned int schedule_id,
		     enum og_schedule_type type);

#endif
