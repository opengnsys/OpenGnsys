/// @file arrays.js
/// @brief: implementa funciones de uso común para arrays.
/// @date: 2014-10-23

/// @function array_interset
/// @brief Devuelve un array con los elementos comunes a los dos arrays iniciales.
/// @brief Los arrays deben estar ordenados.
/// @param 1 {Array} array ordenado.
/// @param 2 {Array} array ordenado.
/// @return {Array} array con elementos comunes.
/// @date: 2014-10-23
function array_interset (a, b) {
   var ai=0, bi=0;
   var result = new Array();
   while( ai < a.length && bi < b.length )
		if      (a[ai] < b[bi] ){ ai++; }
	else if (a[ai] > b[bi] ){ bi++; }
	else /* they're equal */
	{
		result.push(a[ai]);
		ai++;
		bi++;
	}
   return result;
}





