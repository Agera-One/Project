const formGroups = document.querySelectorAll(".form-group");

formGroups.forEach((group, index) => {
    group.style.animationDelay = `${0.4 + index * 0.08}s`;
});

const infoBox = document.querySelector(".info-box");

infoBox.style.animationDelay = "1s";
