/* 
    <!-- ===============================
        PERINGATAN INPUT TAMBAH PRODUK
    ================================ --> 
*/
const inputCodeAdd = document.getElementById("code");
const warningCodeAdd = document.getElementById("warningCodeAdd");
inputCodeAdd.addEventListener("input", function () {
  if (inputCodeAdd.value.length > 5) {
    warningCodeAdd.style.display = "inline";
    inputCodeAdd.value = inputCodeAdd.value.slice(0, 5);
  } else {
    warningCodeAdd.style.display = "none";
  }
});

const inputNameAdd = document.getElementById("name");
const warningNameAdd = document.getElementById("warningNameAdd");
inputNameAdd.addEventListener("input", function () {
  if (inputNameAdd.value.length > 20) {
    warningNameAdd.style.display = "inline";
    inputNameAdd.value = inputNameAdd.value.slice(0, 20);
  } else {
    warningNameAdd.style.display = "none";
  }
});

const inputPriceAdd = document.getElementById("price");
const warningPriceAdd = document.getElementById("warningPriceAdd");
inputPriceAdd.addEventListener("input", function () {
  if (inputPriceAdd.value.length > 12) {
    warningPriceAdd.style.display = "inline";
    inputPriceAdd.value = inputPriceAdd.value.slice(0, 12);
  } else {
    warningPriceAdd.style.display = "none";
  }
});


/* 
    <!-- ===============================
        PERINGATAN INPUT EDIT PRODUK
    ================================ --> 
*/
const inputCodeEdit = document.getElementById("code");
const warningCodeEdit = document.getElementById("warningCodeEdit");
inputCodeEdit.addEventListener("input", function () {
  if (inputCodeEdit.value.length > 5) {
    warningCodeEdit.style.display = "inline";
    inputCodeEdit.value = inputCodeEdit.value.slice(0, 5);
  } else {
    warningCodeEdit.style.display = "none";
  }
});

const inputNameEdit = document.getElementById("name");
const warningNameEdit = document.getElementById("warningNameEdit");
inputNameEdit.addEventListener("input", function () {
  if (inputNameEdit.value.length > 20) {
    warningNameEdit.style.display = "inline";
    inputNameEdit.value = inputNameEdit.value.slice(0, 20);
  } else {
    warningNameEdit.style.display = "none";
  }
});

const inputPriceEdit = document.getElementById("price");
const warningPriceEdit = document.getElementById("warningPriceEdit");
inputPriceEdit.addEventListener("input", function () {
  if (inputPriceEdit.value.length > 12) {
    warningPriceEdit.style.display = "inline";
    inputPriceEdit.value = inputPriceEdit.value.slice(0, 12);
  } else {
    warningPriceEdit.style.display = "none";
  }
});
