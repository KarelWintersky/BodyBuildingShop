$(document).ready(function(){
    ymaps.ready(ymap_init);
});

function ymap_init() {

	var coord_x = '59.841141';
	var coord_y = '30.252731';
	var zoom = '16';


	var map = new ymaps.Map('map', {
			center: [coord_x, coord_y],
			zoom: zoom
		});
		map.controls.add('zoomControl', { top: 5, left: 5 });

		hmc = new ymaps.GeoObjectCollection(),

	 	marker = new ymaps.Placemark([coord_x, coord_y]);
		hmc.add(marker);
		map.geoObjects.add(hmc);
}