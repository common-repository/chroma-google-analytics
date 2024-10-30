function validateGA(input){
    var id = input.value;
    var valid = (/^(U|u)(A|a)-\d+-\d+$/i).test(id.toString());
    if (!valid) {
        if (!input.parentElement.classList.contains("cga_invalid_id")){
            input.parentElement.className += "cga_invalid_id ";
        }
    } else {
        input.parentElement.classList.remove('cga_invalid_id');
    }
}

window.onload = function () {
    var input = document.getElementById('chroma_googleanalytics-id');
    validateGA(input);

    input.addEventListener('input', function (e) {
        validateGA(input);
    }, false);

};
