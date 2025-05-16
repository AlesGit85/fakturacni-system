/**
 * Hlavní JavaScript soubor pro fakturaèní systém
 */

// Poèkáme na naètení celého dokumentu
document.addEventListener("DOMContentLoaded", function() {
    console.log("Fakturaèní systém - JavaScript naèten");
    
    // Obecné funkce pro celou aplikaci
    setupFormValidation();
    addMobileMenuHandler();
});

/**
 * Základní validace formuláøù
 */
function setupFormValidation() {
    const forms = document.querySelectorAll("form");
    
    forms.forEach(form => {
        form.addEventListener("submit", function(event) {
            let isValid = true;
            
            // Kontrola povinných polí
            const requiredFields = form.querySelectorAll("[required]");
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add("error");
                } else {
                    field.classList.remove("error");
                }
            });
            
            // Pokud formuláø není validní, zabráníme odeslání
            if (!isValid) {
                event.preventDefault();
                showAlert("Prosím vyplòte všechna povinná pole.");
            }
        });
    });
}

/**
 * Zobrazí hlášku uživateli
 * @param {string} message Text hlášky
 * @param {string} type Typ hlášky (alert, success, info)
 */
function showAlert(message, type = "alert") {
    // Zkontrolujeme, zda již existuje alert
    let alertElement = document.querySelector(".js-alert");
    
    if (!alertElement) {
        // Vytvoøíme nový element
        alertElement = document.createElement("div");
        alertElement.className = `${type} js-alert`;
        
        // Vložíme ho na zaèátek hlavního obsahu
        const mainContent = document.querySelector("main");
        mainContent.insertBefore(alertElement, mainContent.firstChild);
    } else {
        // Aktualizujeme tøídu existujícího elementu
        alertElement.className = `${type} js-alert`;
    }
    
    // Nastavíme text
    alertElement.textContent = message;
    
    // Po 5 sekundách alert skryjeme
    setTimeout(() => {
        alertElement.style.opacity = "0";
        setTimeout(() => {
            alertElement.remove();
        }, 300);
    }, 5000);
}

/**
 * Pøidání lepší podpory pro mobilní menu
 */
function addMobileMenuHandler() {
    // Na mobilech pøidáme možnost sbalit/rozbalit menu
    if (window.innerWidth <= 768) {
        const nav = document.querySelector("nav");
        const header = document.querySelector("header");
        
        if (nav && header) {
            // Vytvoøíme tlaèítko pro rozbalení menu
            const menuToggle = document.createElement("button");
            menuToggle.className = "menu-toggle";
            menuToggle.innerHTML = "? Menu";
            menuToggle.setAttribute("aria-label", "Pøepnout menu");
            
            // Pøidáme ho pøed menu
            header.insertBefore(menuToggle, nav);
            
            // Skryjeme menu na zaèátku
            nav.classList.add("collapsed");
            
            // Pøidáme posluchaè události pro rozbalení/sbalení menu
            menuToggle.addEventListener("click", function() {
                nav.classList.toggle("collapsed");
                
                if (nav.classList.contains("collapsed")) {
                    menuToggle.innerHTML = "? Menu";
                } else {
                    menuToggle.innerHTML = "? Zavøít";
                }
            });
        }
    }
}
