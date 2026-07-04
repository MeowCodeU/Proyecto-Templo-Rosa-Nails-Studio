/* ===================================================== */
/* EFECTO DOCK / NAVBAR */
/* ===================================================== */

const dockMenu = document.getElementById("dockMenu");

function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
}

if (dockMenu) {
    const dockItems = Array.from(
        dockMenu.querySelectorAll(".dock-item")
    );

    function applyDockEffect(mouseX) {
        dockItems.forEach((item) => {
            const rect = item.getBoundingClientRect();
            const itemCenter = rect.left + rect.width / 2;
            const distance = Math.abs(mouseX - itemCenter);

            const maxDistance = 170;
            const influence = clamp(
                1 - distance / maxDistance,
                0,
                1
            );

            const scale = 1 + influence * 0.34;
            const lift = influence * 12;
            const labelLift = influence * 2;

            const baseY =
                getComputedStyle(item)
                    .getPropertyValue("--base-y")
                    .trim() || "0px";

            item.style.transform =
                `translateY(calc(${baseY} - ${lift}px))`;

            const icon = item.querySelector(".icon");
            const label = item.querySelector(".label");

            if (icon) {
                icon.style.transform = `scale(${scale})`;
            }

            if (label) {
                label.style.transform =
                    `translateY(${-labelLift}px) ` +
                    `scale(${1 + influence * 0.04})`;

                label.style.opacity =
                    `${0.86 + influence * 0.14}`;
            }
        });
    }

    function resetDockEffect() {
        dockItems.forEach((item) => {
            const baseY =
                getComputedStyle(item)
                    .getPropertyValue("--base-y")
                    .trim() || "0px";

            item.style.transform = `translateY(${baseY})`;

            const icon = item.querySelector(".icon");
            const label = item.querySelector(".label");

            if (icon) {
                icon.style.transform = "scale(1)";
            }

            if (label) {
                label.style.transform =
                    "translateY(0) scale(1)";

                label.style.opacity = "1";
            }
        });
    }

    dockMenu.addEventListener("mousemove", function (event) {
        applyDockEffect(event.clientX);
    });

    dockMenu.addEventListener("mouseleave", function () {
        resetDockEffect();
    });
}


/* ===================================================== */
/* PREVIEW DE FOTO DE PERFIL */
/* ===================================================== */

const inputFotoPerfil =
    document.getElementById("inputFotoPerfil");

const previewFoto =
    document.getElementById("previewFoto");

if (inputFotoPerfil && previewFoto) {
    previewFoto.addEventListener("click", function () {
        inputFotoPerfil.click();
    });

    inputFotoPerfil.addEventListener("change", function () {
        const archivo = this.files[0];

        if (archivo) {
            const lector = new FileReader();

            lector.onload = function (event) {
                previewFoto.src = event.target.result;

                const fotoHeader =
                    document.querySelector(
                        ".user-session-photo"
                    );

                if (fotoHeader) {
                    fotoHeader.src =
                        event.target.result;
                }
            };

            lector.readAsDataURL(archivo);
        }
    });
}