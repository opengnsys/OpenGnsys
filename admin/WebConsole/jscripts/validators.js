/*
 * Copyright (C) 2011 Daniel Garcia <danigm@wadobo.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


function validate_expr(value, epx) {
	var expr = new RegExp(epx);
	return (value.search(expr) == 0);
}

function validate_number(value) {
	return validate_expr(value, "^\d*$");
}

function validate_alphanum(value) {
	return validate_expr(value, "^\w*$");
}

function validate_notnull(value) {
	return validate_expr(value, "^.+$") && !validate_expr(value, "^\s*0\s*$");
}

function validate_number_notnull(value) {
	return validate_number(value) && validate_notnull(value);
}

function validate_alphanum_notnull(value) {
	return validate_number(value) && validate_notnull(value);
}

function validation_highlight(field) {
	field.focus();
	field.style.border = "1px solid red";
	field.style.background = "#fee";
}
