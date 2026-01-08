function generarTablaPaginada(selector, cabeceras, contenido, numeroPaginas, idTabla, nombreArchivo, pie = []) {
    $(selector).empty();
    
    // Agregar estilos CSS dinámicamente
    const style = `
        <style>
            .table-responsive-jireh thead {
                position: sticky;
                top: 0;
                background-color: white;
                z-index: 1;
            }
            .table-responsive-jireh tfoot {
                position: sticky;
                top: 0;
                background-color: white;
                z-index: 1;
            }
        </style>`;
    $('head').append(style);

    let tema = localStorage.getItem('U_TEMA');

    let table = $('<table id="' + idTabla + '" class="table table-striped table-bordered table-hover table-condensed"></table>');
    let thead = $('<thead><tr></tr></thead>');
    $.each(cabeceras, function (index, cellData) {
        thead.find('tr').append('<th class="bg-'+tema+'">' + cellData + '</th>');
    });
    table.append(thead);
    let tbody = $('<tbody></tbody>');
    table.append(tbody);

    
    if (pie.length > 0) {
        let tfoot = $('<tfoot></tfoot>');
        let row = $('<tr></tr>');
        
        $.each(pie, function (index, cellContent) {
            let cell = $('<th></th>').html(cellContent);
            
            // Verifica si el contenido es un número (entero o flotante)
            if (!isNaN(cellContent) && cellContent !== null && cellContent !== "") {
                cell.css('text-align', 'right');
            }
            
            row.append(cell);
        });
        
        tfoot.append(row);
        table.append(tfoot);
    }

    var sHtml = `<div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" id="input_busqueda_${idTabla}" placeholder="Buscar..." class="form-control">
                                </div>
                                <div class="col-md-9">
                                <div class="btn-group" role="group" aria-label="...">
                                    <button id="exportar_excel_${idTabla}" class="btn btn-success " type="button"><i class="fa-solid fa-file-excel"></i></button>
                                    <button id="copiar_informacion_${idTabla}" class="btn btn-primary " type="button"><i class="fa-solid fa-clipboard"></i></button>
                                </div>
                            </div>
                            <br>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive table-responsive-jireh" id="div_muestra_table_${idTabla}" style="position: relative; max-height: 550px; overflow-y: scroll;">
                                    </div>
                                </div>
                            </div>
                            <br><br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="paginacion_${idTabla}" id="paginacion_${idTabla}">
                                        <!-- Aquí se mostrarán los controles de paginación -->
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="texto_registros_${idTabla}" id="texto_registros_${idTabla}">
                                        <!-- Aquí se mostrarán el numero de registros -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>`;
    
    $(selector).append(sHtml);
    $('#div_muestra_table_'+idTabla+'').append(table);
    
    if (contenido.length > 0) {
        let jsonData = contenido;
        let totalRecords = jsonData.length;
        let currentPage = 1;
        let searchTerm = '';

        // Mostrar la primera página al cargar la página
        mostrarPagina(tbody, jsonData, cabeceras, numeroPaginas, currentPage, searchTerm, idTabla);

        // Manejar el cambio en el input de búsqueda
        $('#input_busqueda_' + idTabla).on('input', function () {
            searchTerm = $(this).val();
            currentPage = 1; // Volver a la primera página al realizar una nueva búsqueda
            mostrarPagina(tbody, jsonData, cabeceras, numeroPaginas, currentPage, searchTerm, idTabla);
        });

        // Manejar el clic en el botón de exportación a Excel
        $('#exportar_excel_'+idTabla).on('click', function () {
            exportarAExcel(jsonData, nombreArchivo, cabeceras, pie);
        });
        $('#copiar_informacion_'+idTabla).on('click', function () {
            copiarContenidoTabla(idTabla);
        });
    }
}

function filtrarDatos(data, searchTerm) {
    return data.filter(function (item) {
        return Object.values(item).some(value =>
            String(value).toLowerCase().includes(searchTerm.toLowerCase())
        );
    });
}

function mostrarPagina(tbody, jsonData, cabeceras, numeroPaginas, currentPage, searchTerm, idTabla) {
    let start = (currentPage - 1) * numeroPaginas;
    let end = start + numeroPaginas;
    let filteredData = filtrarDatos(jsonData, searchTerm);
    tbody.empty();
    
    $.each(filteredData.slice(start, end), function (index, item) {
        let row = $('<tr></tr>');
        
        // Verifica si el item tiene la propiedad clase_tr
        if (item.clase_tr) {
            row.addClass(item.clase_tr);
        }
    
        $.each(item, function (key, contenido_body) {
            // Añadir solo si key no es 'clase_tr' para evitar añadirlo como contenido
            if (key !== 'clase_tr') {
                //let cell = $('<td></td>').html(contenido_body);
                let cell;
                if (typeof contenido_body === 'object' && contenido_body !== null && 'valor' in contenido_body) {
                    // Es un objeto con propiedades
                    cell = $('<td></td>').html(contenido_body.valor);
                    if (contenido_body['background-color']) {
                        cell.css('background-color', contenido_body['background-color']);
                    }
                    if (contenido_body['background']) {
                        cell.css('background', contenido_body['background']);
                    }
                    if (contenido_body['color']) {
                        cell.css('color', contenido_body['color']);
                    }
                } else {
                    // Es un valor normal
                    cell = $('<td></td>').html(contenido_body);
                }
                
                // Verifica si el contenido es un número (entero o flotante)
                if (!isNaN(contenido_body) && contenido_body !== null && contenido_body !== "") {
                    cell.css('text-align', 'right');
                }
                row.append(cell);
            }
        });
        
        tbody.append(row);
    });
    actualizarPaginacion(filteredData.length, numeroPaginas, currentPage, idTabla, jsonData, cabeceras, searchTerm);
    actualizarTexto(start + 1, Math.min(end, filteredData.length), filteredData.length, idTabla);
}


function actualizarPaginacion(totalItems, numeroPaginas, currentPage, idTabla, jsonData, cabeceras, searchTerm) {
    let totalPages = Math.ceil(totalItems / numeroPaginas);
    let paginacionHTML = '';
    let maxButtonsToShow = 10;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtonsToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxButtonsToShow - 1);

    if (startPage > 1) {
        paginacionHTML += '<button type="button" class="pageButton btn btn-default" data-page="1">1</button>';
        paginacionHTML += '<span>...</span>';
    }
    for (let i = startPage; i <= endPage; i++) {
        paginacionHTML += '<button type="button" class="pageButton btn btn-default' + (i === currentPage ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>';
    }
    if (endPage < totalPages) {
        paginacionHTML += '<span>...</span>';
        paginacionHTML += '<button type="button" class="pageButton btn btn-default" data-page="' + totalPages + '">' + totalPages + '</button>';
    }

    $('#paginacion_' + idTabla).html(paginacionHTML);

    // Asegúrate de manejar el evento click después de actualizar el HTML de la paginación
    $('#paginacion_' + idTabla).off('click', '.pageButton').on('click', '.pageButton', function () {
        let selectedPage = parseInt($(this).data('page'));
        mostrarPagina($('#' + idTabla + ' tbody'), jsonData, cabeceras, numeroPaginas, selectedPage, searchTerm, idTabla);
    });
}

function actualizarTexto(startIndex, endIndex, totalRecords, idTabla) {
    $('#texto_registros_' + idTabla).html('Mostrando ' + startIndex + ' - ' + endIndex + ' de ' + totalRecords + ' registros');
}

function exportarAExcel(data, nombreArchivo, cabeceras, pie) {
    // Función para convertir el HTML a texto plano
    function stripHtml(html) {
        var temporalDivElement = document.createElement("div");
        temporalDivElement.innerHTML = html;
        return temporalDivElement.textContent || temporalDivElement.innerText || "";
    }

    // Crear una nueva versión de data con HTML eliminado
    let plainTextData = data.map(row => {
        let plainTextRow = {};
        for (let key in row) {
            if (row.hasOwnProperty(key)) {
                if (typeof row[key] === 'string' && row[key].includes('<')) {
                    plainTextRow[key] = stripHtml(row[key]);
                } else {
                    plainTextRow[key] = row[key];
                }
            }
        }
        return plainTextRow;
    });

    // Crear el worksheet a partir de plainTextData
    let worksheet = XLSX.utils.json_to_sheet(plainTextData);

    // Añadir las cabeceras manualmente
    for (let i = 0; i < cabeceras.length; i++) {
        let cell_address = XLSX.utils.encode_cell({c: i, r: 0});
        worksheet[cell_address] = {t: "s", v: cabeceras[i]};
    }

    // Procesar contenido de datos para establecer el tipo correcto
    let range = XLSX.utils.decode_range(worksheet['!ref']);
    for (let r = 1; r <= range.e.r; r++) { // Empieza desde 1 para evitar cabeceras
        for (let c = 0; c <= range.e.c; c++) {
            let cell_address = XLSX.utils.encode_cell({c: c, r: r});
            let cell = worksheet[cell_address];
            if (cell) {
                if (!isNaN(cell.v) && cell.v !== null && cell.v !== "") {
                    cell.t = 'n'; // número
                    cell.v = parseFloat(cell.v); // convertir a número
                } else {
                    cell.t = 's'; // texto
                }
            }
        }
    }

    // Añadir el pie de página manualmente
    let lastRow = range.e.r + 1; // Última fila después de los datos
    for (let i = 0; i < pie.length; i++) {
        let cell_address = XLSX.utils.encode_cell({c: i, r: lastRow});
        let cellType = !isNaN(pie[i]) && pie[i] !== null && pie[i] !== "" ? "n" : "s"; // n para número, s para texto
        worksheet[cell_address] = {t: cellType, v: cellType === 'n' ? parseFloat(pie[i]) : pie[i]};
    }

    // Obtener nombre y apellido desde localStorage
    let nombre = localStorage.getItem('U_NOMBRE');
    let apellido = localStorage.getItem('U_APELLIDO');

    // Añadir la hora de generación del reporte y el nombre del generador
    let currentDateTime = new Date().toLocaleString();
    let generatedByCellAddress = XLSX.utils.encode_cell({c: 0, r: lastRow + 1});
    worksheet[generatedByCellAddress] = {t: "s", v: `Reporte generado por: ${nombre} ${apellido} el: ${currentDateTime}`};

    // Ajustar el rango si es necesario
    range.e.r = lastRow + 1;
    worksheet['!ref'] = XLSX.utils.encode_range(range);

    // Crear el libro y agregar el worksheet
    let workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, nombreArchivo);

    // Guardar el archivo
    XLSX.writeFile(workbook, `${nombreArchivo}.xlsx`);
}

function copiarContenidoTabla(tablaId) {
    // Obtener la tabla por su ID
    let tabla = document.getElementById(tablaId);

    if (!tabla) {
        console.error(`No se encontró una tabla con el ID '${tablaId}'.`);
        return;
    }

    // Crear un elemento textarea oculto para copiar el contenido
    let textarea = document.createElement('textarea');
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.width = '2em';  // Más que suficiente para contener el contenido
    textarea.style.height = '2em'; // Más que suficiente para contener el contenido
    textarea.style.padding = '0';
    textarea.style.border = 'none';
    textarea.style.outline = 'none';
    textarea.style.boxShadow = 'none';
    textarea.style.background = 'transparent';

    // Obtener el contenido de la tabla como texto
    let tablaTexto = '';
    let filas = tabla.rows;

    for (let i = 0; i < filas.length; i++) {
        let fila = filas[i];
        for (let j = 0; j < fila.cells.length; j++) {
            tablaTexto += fila.cells[j].innerText;
            if (j < fila.cells.length - 1) {
                tablaTexto += '\t'; // Separador de columna
            }
        }
        tablaTexto += '\n'; // Separador de fila
    }

    // Asignar el contenido al textarea y agregarlo al DOM
    textarea.value = tablaTexto;
    document.body.appendChild(textarea);

    // Seleccionar y copiar el contenido del textarea al portapapeles
    textarea.select();
    document.execCommand('copy');

    // Eliminar el textarea del DOM
    document.body.removeChild(textarea);

    // Mostrar notificación flotante de éxito por 2 segundos
    let notification = document.createElement('div');
    notification.textContent = '¡Contenido copiado al portapapeles!';
    notification.style.position = 'fixed';
    notification.style.top = '250px'; // Posición vertical deseada
    notification.style.left = '50%'; // Centrado horizontalmente
    notification.style.transform = 'translateX(-50%)';
    notification.style.padding = '10px 15px';
    notification.style.backgroundColor = '#4CAF50'; // Color de fondo verde
    notification.style.color = 'white';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = '9999'; // Alta capa z-index para estar sobre todo
    notification.style.transition = 'opacity 0.5s ease-out, visibility 0.5s ease-out';
    notification.style.opacity = '0';
    notification.style.visibility = 'hidden';

    document.body.appendChild(notification);

    // Mostrar la notificación
    setTimeout(function() {
        notification.style.opacity = '1';
        notification.style.visibility = 'visible';
    }, 100);

    // Ocultar y eliminar la notificación después de 2 segundos
    setTimeout(function() {
        notification.style.opacity = '0';
        notification.style.visibility = 'hidden';
        setTimeout(function() {
            document.body.removeChild(notification);
        }, 500); // Esperar 0.5 segundos para que termine la transición
    }, 2000); // Mostrar la notificación durante 2 segundos
}
