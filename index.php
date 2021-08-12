<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Ardsocial</title>
	<meta name="viewport" content="width=device-width,initial-scale=1.0"> 
	<link rel="stylesheet" href="assets/style.css?v=3">
</head>
<body>
	<div class="wrapper">
		<header>
			<a href="#">Ardsocial</a>
		</header>

		<main>
		
		</main>

		<footer>
			Контакты
		</footer>
	</div>
	
	<script>
		function getNews(){
				let promise = fetch('/ajax.php/?count=9');
				promise.then(response => {
					return response.json();
				}).then(data => {
							if(checkData(data,'auth')){
								removeAndAddBlock(data);
								 modifyLinks();
								 authUser();
							}	
							if(checkData(data,'profile')){
								removeAndAddBlock(data);	
								modifyLinks();	
							}
				})
		}

		function authUser(){
			let form = document.querySelector('form');
			form.addEventListener('submit', function(event){
				let login = getSelector('input[data-name="login"]');
				let password = getSelector('input[data-name="password"]');
				let formData = new FormData();
				let objectData = {'login':login,
							  'password':password,
							  'count':'auth'	
							 };
				formDataSet(formData, objectData);
				let promise = addFetch(formData);
				promise.then(response => {
					return response.json();
				}).then(data => {
						checkProfileAndAuth(data);
				})
				event.preventDefault();
			})
		}

		function modifyLinks(){
			 let a = document.querySelectorAll('a');
			 for(let href of a){
			 	href.addEventListener('click', function(event){
			 		let linkParam = this.dataset.value;
			 		let searchParams = new URLSearchParams('link=' + linkParam);
					let promise = addFetch(searchParams);
					promise.then(response => {
						return response.json();
					}).then(data => {
						checkDataGlobal(data);
					})
			 		event.preventDefault();
			 	})
			 }
		}

		function registerUser(){
			let form = document.querySelector('form');
			form.addEventListener('submit', function(event){
				let login = getSelector('input[data-name="login"]');
				let password = getSelector('input[data-name="password"]');
				let password_confirm = getSelector('input[data-name="password_confirm"]');
				let name = getSelector('input[data-name="name"]');
				let family = getSelector('input[data-name="family"]');
					
				let objectData = {
									'login':login,
				  			  		'password':password, 
				  			 		'password_confirm':password_confirm, 
				  			 		'name':name,
				  			 		'family':family, 
				  			  		'count': 'reg'
				  				};
				let formData = new FormData();
				formDataSet(formData, objectData);
				let promise = addFetch(formData);
				promise.then(response => {
					return response.json();
				}).then(data => {
						checkProfileAndReg(data);		
				})
				event.preventDefault();
			})
		}

		function sendMessage(){
			let form = document.querySelector('form');
			form.addEventListener('submit', function(event){
				let message = getSelector('textarea[data-name="message"]');
				let formData = new FormData();
				let objectData = {
									'message':message,
							  		'count':'message'
							   	};
				formDataSet(formData, objectData);
				let promise = addFetch(formData);
				promise.then(response => {
					return response.json();
				}).then(data => {
						if(checkData(data, 'dialog')){
							removeAndAddBlock(data);
							modifyLinks();
							sendMessage();
					}		
				})
				event.preventDefault();
			})
		}

		function getSelector(selector){
			return document.querySelector(selector).value;
		}

		function checkData(data, value){
			return data[1] == value;
		}

		function checkProfileAndAuth(data){
			if(checkData(data,'auth')){
				removeAndAddBlock(data);	
				authUser();
				modifyLinks();
			}
			if(checkData(data,'proile')){
				removeAndAddBlock(data);	
				modifyLinks();	
			}		
		}

		function checkProfileAndReg(data){
			if(checkData(data, 'reg')){
				removeAndAddBlock(data);
				registerUser();
				modifyLinks();	
			}
			if(checkData(data, 'profile')){
				removeAndAddBlock(data);
				modifyLinks();	
			}	
		}

		function checkDataGlobal(data){
			if(checkData(data,'reg')){
				removeAndAddBlock(data);
				registerUser();
  				modifyLinks();
			}
			if(checkData(data,'auth')){
				getNews();
				modifyLinks();
			}
			if(checkData(data,'message')){
				removeAndAddBlock(data);
				modifyLinks();
			}
			if(checkData(data,'profile')){
				removeAndAddBlock(data);
				modifyLinks();
			}
			if(checkData(data,'dialog')){
				removeAndAddBlock(data);
				modifyLinks();
				sendMessage();
			}
			if(checkData(data,'logout')){
				removeAndAddBlock(data);
				modifyLinks();
				sendMessage();
			}
		}

		function formDataSet(formData, object){
			for(let elem in object){
				formData.set(elem, object[elem]);
			}
		}

		function removeAndAddBlock(data){
			if((document.querySelector('.block'))){
				document.querySelector('main').removeChild(document.querySelector('.block'));
			}
			let div = document.createElement('div');
			div.classList.add('block');
			div.innerHTML = data[0];
			document.querySelector('main').appendChild(div);		
		}

		function addFetch(body){
			return fetch('/ajax.php/', {
					method:'POST',
					body:body,
				});
		}

		getNews();
	</script>
</body>
</html>