(function(){
	'use strict';
	angular.module(appName).service("ServerDchpResource", ServerDchpResource);

	ServerDchpResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function ServerDchpResource($resource, $q, gbnBaseResource, API_URL){
		var methods = {
			getDhcp: getDhcp,
		}
		var resource = $resource(API_URL+"/core/dhcp.json", {}, {'update': {method:'PATCH'}});

		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});

		function getDhcp(){
			return $q(function(resolve, reject){
				resolve({
					text: ' ddns-update-style none;\n\
							option domain-name "example.org";\n\
							log-facility local7;\n\
							not-authoritative;\n\
							\n\
							subnet 172.16.53.0 netmask 255.255.255.0 {\n\
							    option domain-name-servers 172.16.3.1;\n\
							    option routers 172.16.53.254;\n\
							    option broadcast-address 172.16.53.255;\n\
							    default-lease-time 600;\n\
							    max-lease-time 7200;\n\
							    next-server 172.16.53.202;\n\
							    filename "grldr";\n\
							    use-host-decl-names on;\n\
							\n\
							\n\
							\n\
							####################Prueba MÃ¡quina Virtual ordenador de Jose, arranque PXE ###########3\n\
							#   host pc53-192 { hardware ethernet 08:00:27:0F:8C:7B; fixed-address 172.16.53.192; }\n\
							####################Prueba MÃ¡quina Virtual ordenador de Jose, arranque PXE ###########3\n\
							#   host pc53-190 { hardware ethernet 08:00:27:70:2F:A1; fixed-address 172.16.53.190; }\n\
							\n\
							\n\
							##### A300 (151 - 170)\n\
							   host pc53-151 { hardware ethernet 00:1E:33:61:49:B8; fixed-address 172.16.53.151; }\n\
							   host pc53-152 { hardware ethernet 00:1E:33:61:47:90; fixed-address 172.16.53.152; }\n\
							   host pc53-153 { hardware ethernet 00:1E:33:61:4A:20; fixed-address 172.16.53.153; }\n\
							   host pc53-154 { hardware ethernet 00:1E:33:61:4A:77; fixed-address 172.16.53.154; }\n\
							   host pc53-155 { hardware ethernet 00:1E:33:61:43:EE; fixed-address 172.16.53.155; }\n\
							   host pc53-156 { hardware ethernet 00:1E:33:60:FD:45; fixed-address 172.16.53.156; }\n\
							   host pc53-157 { hardware ethernet 00:1E:33:5C:33:B1; fixed-address 172.16.53.157; }\n\
							\n\
							##### L500 (171 - 190)\n\
							   host pc53-171 { hardware ethernet 00:23:5A:F5:E3:58; fixed-address 172.16.53.171; }\n\
							   host pc53-172 { hardware ethernet 00:23:5A:F5:E3:7B; fixed-address 172.16.53.172; }\n\
							   host pc53-173 { hardware ethernet 00:23:5A:F5:E3:74; fixed-address 172.16.53.173; }\n\
							   host pc53-174 { hardware ethernet 00:23:5A:F5:E6:17; fixed-address 172.16.53.174; }\n\
							   host pc53-175 { hardware ethernet 00:23:5A:F5:E6:45; fixed-address 172.16.53.175; }\n\
							   host pc53-176 { hardware ethernet 00:23:5A:F5:E6:60; fixed-address 172.16.53.176; }\n\
							   host pc53-177 { hardware ethernet 00:23:5A:F5:E6:7A; fixed-address 172.16.53.177; }\n\
							   host pc53-178 { hardware ethernet 00:23:5A:F5:E8:EB; fixed-address 172.16.53.178; }\n\
							   host pc53-179 { hardware ethernet 00:23:5A:F5:E2:55; fixed-address 172.16.53.179; }\n\
							\n\
							#### Prueba gigabyte nano-itx\n\
							   host pc53-180 { hardware ethernet 40:8D:5C:31:81:8B; fixed-address 172.16.53.180; }\n\
							#### Prueba maquina virtual\n\
							   host pc53-181 { hardware ethernet 08:00:27:8F:C0:68; fixed-address 172.16.53.181; }\n\
							#### Prueba maquina virtual\n\
							   host pc53-182 { hardware ethernet 00:23:24:BF:F2:F7; fixed-address 172.16.53.182; }\n\
							   host pc53-183 { hardware ethernet 00:23:24:BF:F4:B8; fixed-address 172.16.53.183; }\n\
							\n\
							 #----------------- Pruebas Office 2016 en SIC --------------------------------\n\
							  # host pc53-250 {hardware ethernet 00:1B:21:1F:EC:67;fixed-address 172.16.53.250;}\n\
							   host pc53-228 {hardware ethernet 00:1B:21:70:0E:92;fixed-address 172.16.53.228;}\n\
							\n\
							\n\
							################## MAQUINAS SIC DC7800\n\
							# PROTOTIPO\n\
							# host pc53-190 { hardware ethernet 00:23:24:9E:DA:30; fixed-address 172.16.53.190; }\n\
							\n\
							# JOSE ANTONIO GONZALEZ ROMERO 172.16.140.15\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:98:16:94; fixed-address 172.16.53.191; }\n\
							# JOSE MANUEL VELEZ RIVERA 172.16.140.36\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:98:16:C0; fixed-address 172.16.53.191; }\n\
							# ANTONIO JOSE REDONDO GARCIA 172.16.140.135\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:98:16:A0; fixed-address 172.16.53.191;  }\n\
							# JAVIER CASADO ROMERO\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:1E:C8; fixed-address 172.16.53.191; }\n\
							# JOSE LUIS GESTIDO CABRERa  172.16.140.43\n\
							 host pc53-191 {hardware ethernet 00:0F:FE:98:13:52; fixed-address 172.16.53.191; }\n\
							# AMELIA MARTIN\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:1E:24; fixed-address 172.16.53.191; }\n\
							# VICTORIA COLCHERO CLARES   172.16.140.22\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:11:14; fixed-address 172.16.53.191; }\n\
							# PABLO ZARAUZA 172.16.140.20\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:11:3C; fixed-address 172.16.53.191; }\n\
							# ANTONIO SERAFIN RODRIGUEZ MARTIN  172.16.140.10\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:98:10:9E; fixed-address 172.16.53.191; }\n\
							# DIONISIO CARDENO VERDEJO 172.16.140.41\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:11:36; fixed-address 172.16.53.191; }\n\
							# MANUEL JESUS MANAS\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:0F:2C; fixed-address 172.16.53.191;}\n\
							# MARIA ISABEL BAREA\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:1E:DE; fixed-address 172.16.53.191;}\n\
							# FRANCISCO CUESTA CANO\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:1E:D6; fixed-address 172.16.53.191;}\n\
							# JOAQUIN DORADO 172.16.140.14\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:98:16:FE; fixed-address 172.16.53.191;}\n\
							# AURORA PARRALO\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:99:1E:DE; fixed-address 172.16.53.191;}\n\
							# VANESA\n\
							#  host pc53-191 {hardware ethernet 00:0F:FE:98:70:39; fixed-address 172.16.53.191;}\n\
							# FRANCISCO JARDIN 172.16.140.50\n\
							#  host pc53-191 {hardware ethernet 00:0F:FE:98:12:93; fixed-address 172.16.53.191;}\n\
							# SEBASTIAN CERREJON PEREZ 172.16.140.87\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:98:15:E2; fixed-address 172.16.53.191;}\n\
							# JOSE MARIA PONCE\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:98:16:F2; fixed-address 172.16.53.191;}\n\
							# JOSE MIGUEL GARCIA 172.16.140.48\n\
							# host pc53-191 {hardware ethernet 00:0F:FE:98:14:78; fixed-address 172.16.53.191;}\n\
							}'
				}
				);
			});
		}

	}
})();