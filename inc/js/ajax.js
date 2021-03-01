document.addEventListener("DOMContentLoaded", function (e) {

	if (document.getElementById('ajax')) {
		let categorie = document.querySelectorAll('.categorie');
		let select = document.querySelectorAll('select');
		let inputRange = document.querySelectorAll('input[type="range"]');
		let datePicker = document.querySelectorAll('.datepicker-input , .datepicker-cell');


		//Test si le name existe dans les params pour éviter les injections
		function testParam(params, value) {
			let test = false;
			for (let param in params) {
				if (param === value) {
					test = true;
				}
			}
			return test;
		}

		let params = {
			'categorie': '',
			'ville': '',
			'capacite': '',
			'prix': '',
			'date_arrivee': '',
			'date_depart': '',
			'order' : '',
			'limit' : ''
		}

		for (let i = 0; i < categorie.length; i++) {
			categorie[i].addEventListener('click', function (e) {
				let get = (categorie[i].href).split("?");
				console.log(get);
				if (get[get.length - 1] === 'categorie=') {
					params['categorie'] = '';
				} else {
					params['categorie'] = get[get.length - 1];
				}
				e.preventDefault();

				setParam(params);
			});
		}
		for (let i = 0; i < select.length; i++) {
			select[i].addEventListener('change', function (e) {
				let get = select[i].value;
				if (testParam(params, select[i].name)) {
					if (get == '') {
						params[select[i].name] = '';
					} else {
						params[select[i].name] = select[i].name + '=' + get;
					}
					e.preventDefault();

					setParam(params);
				};
			});
		}
		for (let i = 0; i < inputRange.length; i++) {
			inputRange[i].addEventListener('input', function (e) {
				if (testParam(params, inputRange[i].name)) {
					let get = inputRange[i].value;
					if (get == '') {
						params[inputRange[i].name] = '';
					} else {
						params[inputRange[i].name] = inputRange[i].name + '=' + get;
					}
					e.preventDefault();

					setParam(params);
				};
			});
		}
		for (let i = 0; i < datePicker.length; i++) {
			datePicker[i].addEventListener('click', function (e) {
				function waitValue() {
					for (let i = 0; i < datePicker.length; i++) {
						if (testParam(params, datePicker[i].name)) {
							let get = datePicker[i].value;
							if (get === '') {
								params[datePicker[i].name] = '';
							} else {
								get = dayjs(get, 'DD-MM-YYYY').format('YYYY-MM-DD HH:mm:ss');
								params[datePicker[i].name] = datePicker[i].name + '=' + get;
							}
							e.preventDefault();
							setParam(params);
						};
					}
				}
				setTimeout(waitValue, 100); 

			});
		}


		function setParam(params) {
			let filtre = 'inc/produit.php?';
			for (let param in params) {
				if (params[param] !== '') {
					if (filtre !== 'inc/produit.php?') {
						filtre += '&'
					}
					filtre += params[param];
				}
			}
			console.log(filtre);
			ajax(filtre);
		}
		function ajax(filtre) {
			if (window.XMLHttpRequest) { //SI la classe est disponible sur mon navigateur

				var xhr = new XMLHttpRequest(); //pour la plupart des navigateurs
			}
			else {
				var xhr = new ActiveXObject("Microsoft.XMHTTP"); // Pour les anciennes version de explorer..
			}
			xhr.open('GET', filtre, true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
			xhr.send();

			xhr.onreadystatechange = function () {
				if (xhr.readyState == 4 && xhr.status == 200) {
					document.getElementById('resultat').innerHTML = xhr.responseText;
				}
			}
		}
	}
});
