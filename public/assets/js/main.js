jQuery("#crud_stores_form_create").submit(function (e) {
    e.preventDefault();

    jQuery.ajax({
        url: ajax_var.url,
        type: "POST", // podría ser get, post, put o delete.
        data: jQuery(this).serialize(),
        beforeSend: function () {
            alert("voy a enviar información");
        },
        success: function ($response) {
            console.log($response);
        },
        error: function (jqXHR, exception) {
            var msg = "";
            if (jqXHR.status === 0) {
                msg = "Not connect.n Verify Network.";
            } else if (jqXHR.status == 404) {
                msg = "Requested page not found. [404]";
            } else if (jqXHR.status == 500) {
                msg = "Internal Server Error [500].";
            } else if (exception == "parsererror") {
                msg = "Requested JSON parse failed.";
            } else if (exception === "timeout") {
                msg = "Time out error.";
            } else if (exception === "abort") {
                msg = "Ajax request aborted.";
            } else {
                msg = "Uncaught Error.n" + jqXHR.responseText;
            }
            alert(msg);
        },
    });
});

function validateFileType() {
    var fileName = document.getElementById("imagen").value;
    var idxDot = fileName.lastIndexOf(".") + 1;
    var extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
    if (extFile == "jpg" || extFile == "jpeg" || extFile == "png") {
        //TO DO
    } else {
        alert("Solamente archivos .jpg/jpeg y .png están permitidos!");
        var fileName = (document.getElementById("imagen").value = "");
    }
}
// Obtener elementos del DOM
const dropArea = document.getElementById("drop-area");
const uploadInput = document.getElementById("imagen");
const previewContainer = document.getElementById("preview-container");

// Manejar el evento de selección de archivos
uploadInput.addEventListener("change", handleFileSelect);

// Manejar los eventos de arrastrar y soltar archivos
dropArea.addEventListener("dragenter", handleDragEnter);
dropArea.addEventListener("dragover", handleDragOver);
dropArea.addEventListener("dragleave", handleDragLeave);
dropArea.addEventListener("drop", handleDrop);

// Manejar el evento de selección de archivos
function handleFileSelect(event) {
    const files = event.target.files;
    previewImages(files);
}

// Manejar los eventos de arrastrar y soltar archivos
function handleDragEnter(event) {
    event.preventDefault();
    dropArea.classList.add("drag-over");
}

function handleDragOver(event) {
    event.preventDefault();
}

function handleDragLeave(event) {
    dropArea.classList.remove("drag-over");
}

function handleDrop(event) {
    event.preventDefault();
    dropArea.classList.remove("drag-over");
    const files = event.dataTransfer.files;
    previewImages(files);
}

// Mostrar las imágenes seleccionadas o arrastradas en la lista de previsualización
function previewImages(files) {
    const maxFiles = 4;
    const totalFiles = previewContainer.children.length + files.length;

    if (totalFiles > maxFiles) {
        alert(`Solo se puede subir como máximo ${maxFiles} foto.`);
        return;
    }

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();

        reader.onload = function (event) {
            const imageSrc = event.target.result;
            const imageCard = createImageCard(imageSrc, file.name);
            previewContainer.appendChild(imageCard);
        };

        reader.readAsDataURL(file);
    }
}

// Crear una tarjeta de imagen para la previsualización
function createImageCard(imageSrc, imageName) {
    const imageCard = document.createElement("li");
    imageCard.className = "image-card";

    const image = document.createElement("img");
    image.src = imageSrc;
    image.alt = imageName;
    image.className = "preview";
    image.style.width = "70px";
    image.style.height = "70px";

    const imageInfo = document.createElement("div");
    imageInfo.className = "image-info";

    const imageNameContainer = document.createElement("div");
    imageNameContainer.className = "image-name-container";

    const imageNameText = document.createElement("p");
    imageNameText.className = "image-name";
    imageNameText.textContent = imageName;
    imageNameText.style.overflow = "hidden";
    imageNameText.style.textOverflow = "ellipsis";
    imageNameText.style.whiteSpace = "nowrap";
    imageNameText.style.maxWidth = "100px";

    const deleteButton = document.createElement("button");
    deleteButton.className = "delete-button";
    deleteButton.textContent = "Eliminar";
    deleteButton.addEventListener("click", function () {
        imageCard.remove();
    });

    imageNameContainer.appendChild(imageNameText);
    imageInfo.appendChild(imageNameContainer);
    imageInfo.appendChild(deleteButton);

    imageCard.appendChild(image);
    imageCard.appendChild(imageInfo);

    return imageCard;
}
