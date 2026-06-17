/* ===================================================== */
/* REGISTRO-SERVICIO.JS */
/* ===================================================== */

    document.addEventListener("DOMContentLoaded", function () {
      const panelAgendamientos = document.getElementById("panelAgendamientosServicio");
      const panelRegistro = document.getElementById("panelRegistroServicio");

      const btnVolver = document.getElementById("btnVolverAgendamientos");
      const btnRefrescar = document.getElementById("btnRefrescarRegistroServicio");

      const fichaNombreClienta = document.getElementById("fichaNombreClienta");
      const fichaAlergias = document.getElementById("fichaAlergias");
      const fichaObservacionAnterior = document.getElementById("fichaObservacionAnterior");
      const fichaUltimoServicio = document.getElementById("fichaUltimoServicio");
      const fichaUltimaAtencion = document.getElementById("fichaUltimaAtencion");
      const fichaServicioAgendado = document.getElementById("fichaServicioAgendado");
      const fichaValorServicio = document.getElementById("fichaValorServicio");

      const servicioRealizado = document.getElementById("servicioRealizado");
      const totalServicio = document.getElementById("totalServicio");
      const estadoServicio = document.getElementById("estadoServicio");
      const fechaServicio = document.getElementById("fechaServicio");
      const categoriaCatalogo = document.getElementById("categoriaCatalogo");

      const fotoResultadoServicio = document.getElementById("fotoResultadoServicio");
      const previewResultadoServicio = document.getElementById("previewResultadoServicio");

      const insumoUtilizado = document.getElementById("insumoUtilizado");
      const cantidadInsumo = document.getElementById("cantidadInsumo");
      const presentacionInsumo = document.getElementById("presentacionInsumo");
      const btnAgregarInsumo = document.getElementById("btnAgregarInsumo");
      const tbodyInsumosSeleccionados = document.getElementById("tbodyInsumosSeleccionados");

      const tbodyHistorialServicios = document.getElementById("tbodyHistorialServicios");

      const btnGuardarServicio = document.getElementById("btnGuardarServicio");
      const btnGuardarInsumos = document.getElementById("btnGuardarInsumos");
      const btnFinalizarServicio = document.getElementById("btnFinalizarServicio");

      const filtroCatalogoServicio = document.getElementById("filtroCatalogoServicio");
      const catalogoItems = document.querySelectorAll(".catalogo-item");

      const clientas = [
        {
          id: 1,
          nombre: "Camila Prado",
          alergias: "Acrílico / Látex",
          observacionAnterior: "Presentó sensibilidad en la cutícula derecha. Se recomendó evitar torno profundo.",
          ultimoServicio: "Capping con rubber base",
          ultimaAtencion: "15/05/2026",
          servicioAgendado: "Polygel",
          valorServicio: "25$",
          total: "25",
          fechaHoy: "2026-06-02",
          historial: [
            {
              fecha: "15/05/2026",
              foto: "assets/img/LogoTR.png",
              servicio: "Capping con rubber base",
              total: "20$",
              observacion: "Sensibilidad en cutícula derecha."
            },
            {
              fecha: "02/05/2026",
              foto: "assets/img/LogoTR.png",
              servicio: "Retiro + manicura semipermanente",
              total: "15$",
              observacion: "Uñas débiles, se recomendó descanso."
            }
          ]
        },
        {
          id: 2,
          nombre: "María Pérez",
          alergias: "No registradas",
          observacionAnterior: "Sin observaciones relevantes.",
          ultimoServicio: "Pedicura tradicional",
          ultimaAtencion: "02/05/2026",
          servicioAgendado: "Pedicura semipermanente",
          valorServicio: "18$",
          total: "18",
          fechaHoy: "2026-06-02",
          historial: [
            {
              fecha: "02/05/2026",
              foto: "assets/img/LogoTR.png",
              servicio: "Pedicura tradicional",
              total: "12$",
              observacion: "Servicio realizado sin novedad."
            }
          ]
        },
        {
          id: 3,
          nombre: "Valentina Gómez",
          alergias: "Monómero",
          observacionAnterior: "Preferencia por tonos nude y acabado natural.",
          ultimoServicio: "Manicura semipermanente",
          ultimaAtencion: "20/05/2026",
          servicioAgendado: "Capping",
          valorServicio: "22$",
          total: "22",
          fechaHoy: "2026-06-02",
          historial: [
            {
              fecha: "20/05/2026",
              foto: "assets/img/LogoTR.png",
              servicio: "Manicura semipermanente",
              total: "16$",
              observacion: "Acabado natural en tono nude."
            }
          ]
        }
      ];

      let insumosSeleccionados = [
        { insumo: "Rubber base", cantidad: "2", presentacion: "ml" },
        { insumo: "Top coat", cantidad: "1", presentacion: "ml" }
      ];

      function abrirRegistro(id) {
        const clienta = clientas.find(item => item.id === id);
        if (!clienta) return;

        fichaNombreClienta.textContent = clienta.nombre;
        fichaAlergias.textContent = clienta.alergias;
        fichaObservacionAnterior.textContent = clienta.observacionAnterior;
        fichaUltimoServicio.textContent = clienta.ultimoServicio;
        fichaUltimaAtencion.textContent = clienta.ultimaAtencion;
        fichaServicioAgendado.textContent = clienta.servicioAgendado;
        fichaValorServicio.textContent = clienta.valorServicio;

        servicioRealizado.value = clienta.servicioAgendado;
        totalServicio.value = clienta.total;
        estadoServicio.value = "Realizada";
        fechaServicio.value = clienta.fechaHoy;
        categoriaCatalogo.value = clienta.servicioAgendado;

        cargarHistorial(clienta.historial);
        renderizarInsumos();

        panelAgendamientos.classList.add("d-none");
        panelRegistro.classList.remove("d-none");

        btnVolver.classList.remove("d-none");
        btnRefrescar.classList.add("d-none");
      }

      function volverLista() {
        panelRegistro.classList.add("d-none");
        panelAgendamientos.classList.remove("d-none");

        btnVolver.classList.add("d-none");
        btnRefrescar.classList.remove("d-none");
      }

      function cargarHistorial(historial) {
        tbodyHistorialServicios.innerHTML = "";

        historial.forEach(item => {
          const tr = document.createElement("tr");

          tr.innerHTML = `
            <td>${item.fecha}</td>
            <td>
              <img 
                src="${item.foto}" 
                alt="Foto del servicio" 
                style="width:54px; height:54px; object-fit:cover; border-radius:14px; border:2px solid rgba(216,167,177,0.55);"
              >
            </td>
            <td>${item.servicio}</td>
            <td>${item.total}</td>
            <td>${item.observacion}</td>
            <td class="text-center">
              <button class="btn-action btn-edit" type="button" title="Ver detalle">
                <i class="bi bi-eye"></i>
              </button>
            </td>
          `;

          tbodyHistorialServicios.appendChild(tr);
        });
      }

      function renderizarInsumos() {
        tbodyInsumosSeleccionados.innerHTML = "";

        if (insumosSeleccionados.length === 0) {
          tbodyInsumosSeleccionados.innerHTML = `
            <tr>
              <td colspan="4" class="text-center">No hay insumos agregados.</td>
            </tr>
          `;
          return;
        }

        insumosSeleccionados.forEach((item, index) => {
          const tr = document.createElement("tr");

          tr.innerHTML = `
            <td>${item.insumo}</td>
            <td>${item.cantidad}</td>
            <td>${item.presentacion}</td>
            <td class="text-center">
              <button class="btn-action btn-delete btn-eliminar-insumo" type="button" data-index="${index}" title="Eliminar">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          `;

          tbodyInsumosSeleccionados.appendChild(tr);
        });
      }

      function mostrarAlerta(titulo, texto, icono) {
        if (typeof Swal !== "undefined") {
          Swal.fire({
            title: titulo,
            text: texto,
            icon: icono,
            confirmButtonColor: "#D8A7B1"
          });
        } else {
          alert(`${titulo}: ${texto}`);
        }
      }

      document.querySelectorAll(".btn-registrar-servicio").forEach(btn => {
        btn.addEventListener("click", function () {
          abrirRegistro(parseInt(this.dataset.id, 10));
        });
      });

      btnVolver.addEventListener("click", volverLista);

      btnRefrescar.addEventListener("click", function () {
        mostrarAlerta("Actualizado", "La lista fue actualizada en el prototipo.", "success");
      });

      if (fotoResultadoServicio) {
        fotoResultadoServicio.addEventListener("change", function () {
          const archivo = this.files[0];
          if (!archivo) return;
          previewResultadoServicio.src = URL.createObjectURL(archivo);
        });
      }

      btnAgregarInsumo.addEventListener("click", function () {
        const insumo = insumoUtilizado.value;
        const cantidad = cantidadInsumo.value.trim();
        const presentacion = presentacionInsumo.value.trim();

        if (!insumo || !cantidad || !presentacion) {
          mostrarAlerta("Faltan datos", "Seleccione un insumo, cantidad y presentación.", "warning");
          return;
        }

        insumosSeleccionados.push({ insumo, cantidad, presentacion });

        insumoUtilizado.value = "";
        cantidadInsumo.value = "";
        presentacionInsumo.value = "";

        renderizarInsumos();
      });

      document.addEventListener("click", function (event) {
        const botonEliminar = event.target.closest(".btn-eliminar-insumo");
        if (!botonEliminar) return;

        const index = parseInt(botonEliminar.dataset.index, 10);
        insumosSeleccionados.splice(index, 1);
        renderizarInsumos();
      });

      btnGuardarServicio.addEventListener("click", function () {
        mostrarAlerta("Servicio guardado", "El servicio fue registrado correctamente en el prototipo.", "success");
      });

      btnGuardarInsumos.addEventListener("click", function () {
        mostrarAlerta("Insumos guardados", "Los insumos utilizados quedaron registrados en el prototipo.", "success");
      });

      btnFinalizarServicio.addEventListener("click", function () {
        mostrarAlerta("Registro finalizado", "El registro del servicio fue finalizado correctamente.", "success");
      });

      filtroCatalogoServicio.addEventListener("change", function () {
        const filtro = this.value;

        catalogoItems.forEach(item => {
          const servicio = item.dataset.servicio;

          if (filtro === "todos" || servicio === filtro) {
            item.classList.remove("d-none");
          } else {
            item.classList.add("d-none");
          }
        });
      });

      renderizarInsumos();
    });
