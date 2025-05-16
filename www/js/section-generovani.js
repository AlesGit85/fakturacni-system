/**
 * JavaScript pro sekci generování faktur
 */

document.addEventListener("DOMContentLoaded", function () {
    const odberatelSelect = document.getElementById("odberatel-select");
    const manualToggle = document.getElementById("manual-toggle");
    const odberatelField = document.getElementById("odberatel");
    const odberatelManualGroup = document.getElementById("odberatel-manual-group");

    // Kontrola, zda prvky existují (ochrana proti chybám)
    if (!odberatelSelect || !manualToggle || !odberatelField || !odberatelManualGroup) {
        console.error("Nenalezeny potřebné elementy pro výběr odběratele");
        return;
    }

    // Funkce pro přepínání mezi výběrem a ručním zadáním
    function toggleManualInput() {
        const isManual = manualToggle.checked;

        if (isManual) {
            odberatelSelect.disabled = true;
            odberatelManualGroup.style.display = "block";
        } else {
            odberatelSelect.disabled = false;
            if (odberatelSelect.value) {
                // Pokud je vybrán odběratel, tak pole pro ruční zadání skryjeme
                odberatelManualGroup.style.display = "none";
            } else {
                // Jinak ho ponecháme zobrazené
                odberatelManualGroup.style.display = "block";
            }
        }
    }

    // Výchozí stav - předpokládáme ruční zadání, pokud není vybrán odběratel
    toggleManualInput();

    // Obsluha změny v selectu odběratelů
    odberatelSelect.addEventListener("change", function () {
        if (this.value && !manualToggle.checked) {
            // V Nette používáme ID jako hodnotu v selectu, proto získáme data odběratele pomocí fetch API
            fetch(`/Api/odberatel?id=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    if (data && !data.error) {
                        const formattedText = formatujOdberateleProFakturu(data);
                        odberatelField.value = formattedText;
                        odberatelManualGroup.style.display = "none";
                    } else {
                        console.error("Chyba při získávání dat odběratele:", data.error);
                    }
                })
                .catch(error => {
                    console.error("Chyba při získávání dat odběratele:", error);
                });
        } else if (!this.value && !manualToggle.checked) {
            odberatelField.value = "";
            odberatelManualGroup.style.display = "block";
        }
    });

    // Obsluha přepínače pro ruční zadání
    manualToggle.addEventListener("change", toggleManualInput);

    // Funkce pro formátování odběratele
    function formatujOdberateleProFakturu(odberatel) {
        let format = odberatel.nazev + "\n" + odberatel.adresa;

        // Přidáme IČO
        if (odberatel.ico) {
            format += "\nIČO: " + odberatel.ico;
        }

        // Přidáme DIČ, pokud existuje
        if (odberatel.dic) {
            format += "\nDIČ: " + odberatel.dic;
        }

        return format;
    }
});