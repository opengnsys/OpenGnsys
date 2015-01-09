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


// Validar expresión regular.
function validate_expr(value, epx) {
	var expr = new RegExp(epx);
	return (value.search(expr) == 0);
}

// Validar expresión ignorando diferencias entre mayúsculas y minúsculas.
function validate_expr_nocase(value, epx) {
	var expr = new RegExp(epx, "i");
	return (value.search(expr) == 0);
}

function validate_number(value) {
	return validate_expr(value, "^\\d*$");
}

function validate_alphanum(value) {
	return validate_expr(value, "^\\w*$");
}

function validate_notnull(value) {
	return validate_expr(value, "^.+$") && !validate_expr(value, "^\\s*0\\s*$");
}

function validate_number_notnull(value) {
	return validate_number(value) && validate_notnull(value);
}

function validate_alphanum_notnull(value) {
	return validate_number(value) && validate_notnull(value);
}

// Validar dirección IPv4.
function validate_ipadress(value) {
	var octet = '(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])';
	var regex = '^((?:' + octet + '\\.){3}' + octet + ')?$';
	return validate_expr(value, regex);
}

function validate_ipadress_notnull(value) {
	return validate_ipadress(value) && validate_notnull(value);
}

// Validar direccion MAC (sin contar caracteres ":").
function validate_macaddress(value) {
	var regex = '^([0-9a-fA-F]){12}$'
	return validate_expr(value.replace(/:/g,''), regex);
}

function validate_macaddress_notnull(value) {
	return validate_macaddress(value) && validate_notnull(value);
}

// Validar URL.
function validate_url(value) {
	var octet = '(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])';
	var regex = '^((ht|f)tps?:\/\/(([a-z0-9]+([\.\-a-z0-9]+)?\.[a-z]{2,5})|((' + octet + '\\.){3}' + octet + '))(:[0-9]{2,5})?(\/.*)?)?$';
	return validate_expr_nocase(value, regex);

}

function validate_url_notnull(value) {
	return validate_url(value) && validate_notnull(value);
}

function validate_nameimagefile(value) {
	return validate_expr(value, "^[0-9a-zA-Z]*$");
}


function validation_highlight(field) {
	field.focus();
	field.style.border = "1px solid red";
	field.style.background = "#fee";
}

