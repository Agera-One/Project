const formGroups = document.querySelectorAll(".form-group");

formGroups.forEach((group, index) => {
    group.style.animationDelay = `${0.4 + index * 0.05}s`;
});


// document.getElementById('password_confirmation').addEventListener('input', function() {
//     const password = document.getElementById('password').value;
//     const confirm = this.value;
//     if (confirm && password !== confirm) {
//         this.style.borderColor = 'red';
//     } else {
//         this.style.borderColor = '';
//     }
// });
