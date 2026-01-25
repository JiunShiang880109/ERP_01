//百位數加逗點
function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

//F1預設搜尋
document.addEventListener('keydown', GetInput);
		function GetInput(e){
			if(e.key =='F1'){
				e.preventDefault();
				let pdName = document.querySelector('input[name="pdName"]')
				pdName.focus()
			}
      if(e.key =='F2'){
        window.history.back();
			}
		}
// 
function readURL(input){
  if(input.files && input.files[0]){
    var reader = new FileReader();
    reader.onload = function (e) {
      $("#preview").attr('src', e.target.result);
    }
    reader.readAsDataURL(input.files[0]);
  }
}





// function productSearch(){
//   alert(99);
// }