/** ======= Website Aggregator Form ======= **/

document.addEventListener("DOMContentLoaded", function () {
    /** ======= Category wise Subcategory Dropdown list SECTION ======= **/
    document.getElementById("category_id").addEventListener("change", function () {

        // Clear previous materials and shades when category changes
        document.getElementById("material-images").innerHTML = "";
        document.getElementById("shade-images").innerHTML = "";
        document.getElementById("material-section").style.display = "none";
        document.getElementById("shades-section").style.display = "none";

        let categoryId = this.value;
        let subcategoryDropdown = document.getElementById("subcategory");

        // Show a loading option while fetching subcategories
        subcategoryDropdown.innerHTML = '<option value="">Loading...</option>';

        // Update the hidden field with the selected category ID
        document.getElementById("hidden_category_id").value = categoryId;

        if (categoryId) {
            fetch(`/api/get-subcategories/${categoryId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset subcategory dropdown
                    subcategoryDropdown.innerHTML = '<option value="">Select Sub Category</option>';

                    if (data.data && data.data.length > 0) {
                        data.data.forEach(subcategory => {
                            let option = document.createElement("option");
                            option.value = subcategory.id;
                            option.textContent = subcategory.sub_category;
                            subcategoryDropdown.appendChild(option);
                        });
                    } else {
                        subcategoryDropdown.innerHTML = '<option value="">No Subcategories Available</option>';
                    }
                })
                .catch(error => {
                    console.error("Error fetching subcategories:", error);
                    subcategoryDropdown.innerHTML = '<option value="">Error Loading Subcategories</option>';
                });
        }
    });

    /** ======= Subcategory wise Material list SECTION ======= **/
    document.getElementById("subcategory").addEventListener("change", function () {

        // Clear previous materials and shades when subcategory changes
        document.getElementById("material-images").innerHTML = "";
        document.getElementById("shade-images").innerHTML = "";
        document.getElementById("material-section").style.display = "none";
        document.getElementById("shades-section").style.display = "none";

        let subcategoryId = this.value;
        let materialSection = document.getElementById("material-section");
        let materialImagesContainer = document.getElementById("material-images");

        materialImagesContainer.innerHTML = '<p>Loading...</p>';
        materialSection.style.display = "none";

        document.getElementById("hidden_sub_category_id").value = subcategoryId;

        if (subcategoryId) {
            fetch(`/api/get-materials/${subcategoryId}`)
                .then(response => response.json())
                .then(data => {
                    materialImagesContainer.innerHTML = "";

                    if (data.data.length > 0) {
                        data.data.forEach(material => {
                            let imagePath = `/storage/${material.main_img}`;

                            // Collect sub-images from separate columns
                            let subImages = [];
                            if (material.sub_img1) subImages.push(`/storage/${material.sub_img1}`);
                            if (material.sub_img2) subImages.push(`/storage/${material.sub_img2}`);
                            if (material.sub_img3) subImages.push(`/storage/${material.sub_img3}`);
                            if (material.sub_img4) subImages.push(`/storage/${material.sub_img4}`);

                            if (material.video) {
                                subImages.push(`/storage/${material.video}`);
                            }

                            let colDiv = document.createElement("div");
                            colDiv.className = "col-md-3 mb-3";
                            colDiv.innerHTML = `
                                <div class="card">
                                    <img src="${imagePath}" class="card-img-top material-card" data-material-id="${material.id}"
                                     alt="${material.material_name}" style="cursor: pointer;">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div class="col-8 text-truncate">
                                            <p class="card-text m-0">${material.material_name}</p>
                                        </div>
                                        <div class="col-4 text-end">
                                            ${subImages.length > 0 ? `<span class="plus-icon" data-sub-imgs='${JSON.stringify(subImages)}' style="cursor: pointer; text-decoration: underline; color: blue; font-size: 10px">View More</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                            materialImagesContainer.appendChild(colDiv);
                        });

                        materialSection.style.display = "block";

                        // Attach click event to plus icons
                        document.querySelectorAll(".plus-icon").forEach(icon => {
                            icon.addEventListener("click", function () {
                                let subImages = JSON.parse(this.getAttribute("data-sub-imgs"));
                                let mainImg = this.closest(".card").querySelector("img").src;
                                subImages.unshift(mainImg);
                                showSubImagesPopup(subImages);
                            });
                        });
                    } else {
                        materialImagesContainer.innerHTML = '<p>No materials found.</p>';
                    }
                })
                .catch(error => {
                    console.error("Error fetching materials:", error);
                    materialImagesContainer.innerHTML = '<p>Error loading materials.</p>';
                });
        }
    });

    /** ======= Material wise Shades list SECTION ======= **/
    document.getElementById("material-images").addEventListener("click", function (event) {
        let card = event.target.closest(".material-card");
        if (card) {

            document.querySelectorAll(".material-card").forEach(item => {
                item.classList.remove("selected-material");
            });

            // Highlight the selected material
            card.classList.add("selected-material");

            // Clear previous shades when material changes
            document.getElementById("shade-images").innerHTML = "";
            document.getElementById("shades-section").style.display = "none";

            let materialId = card.getAttribute("data-material-id");
            document.getElementById("hidden_material_id").value = materialId;
            loadShades(materialId);
        }
    });

    function loadShades(materialId) {
        let shadesSection = document.getElementById("shades-section");
        let shadeImagesContainer = document.getElementById("shade-images");

        shadeImagesContainer.innerHTML = '<p>Loading...</p>';
        shadesSection.style.display = "none";

        fetch(`/api/get-shades/${materialId}`)
            .then(response => response.json())
            .then(data => {
                shadeImagesContainer.innerHTML = "";

                if (data.data.length > 0) {
                    let cardDiv = document.createElement("div");

                    data.data.forEach((shade) => {
                        let shadeImages = shade.shade_image.map(img => img.shade_img).filter(img => img !== null);

                        let rowDiv = document.createElement("div");
                        rowDiv.className = "mb-3";
                        rowDiv.setAttribute("data-shade-row-id", shade.id);

                        let nameDiv = document.createElement("div");
                        nameDiv.className = "mb-2 font-weight-bold";
                        nameDiv.innerText = `${shade.shade_name}`;

                        let imagesGrid = document.createElement("div");
                        imagesGrid.className = "grid-container";

                        shadeImages.forEach((imgPath, index) => {
                            let shadeImagePath = `${window.location.origin}/storage/${imgPath}`;
                            let imgDiv = document.createElement("div");
                            imgDiv.className = "grid-item";

                            imgDiv.innerHTML = `
                            <img src="${shadeImagePath}" 
                                alt="Shade Image" 
                                width="100" height="100"
                                style="object-fit: contain; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;"
                                data-shade-id="${shade.id}"
                                data-img-id="${index + 1}">
                        `;

                            imgDiv.addEventListener("click", () => handleShadeSelection(imgDiv, shade.id, shadeImagePath));

                            imagesGrid.appendChild(imgDiv);
                        });


                        rowDiv.appendChild(nameDiv);
                        rowDiv.appendChild(imagesGrid);

                        cardDiv.appendChild(rowDiv);

                        // Hidden inputs to store selected shade and image
                        let hiddenShadeIdInput = document.createElement("input");
                        hiddenShadeIdInput.type = "hidden";
                        hiddenShadeIdInput.name = `shades[${shade.id}][shade_id]`;
                        hiddenShadeIdInput.value = shade.id;

                        let hiddenSelectedImgInput = document.createElement("input");
                        hiddenSelectedImgInput.type = "hidden";
                        hiddenSelectedImgInput.id = `hidden_shade_selected_img_${shade.id}`;
                        hiddenSelectedImgInput.name = `shades[${shade.id}][selected_img]`;
                        hiddenSelectedImgInput.value = "";

                        //  cardDiv.appendChild(hiddenShadeIdInput);
                        //  cardDiv.appendChild(hiddenSelectedImgInput);
                        rowDiv.appendChild(hiddenShadeIdInput);
                        rowDiv.appendChild(hiddenSelectedImgInput);
                    });

                    shadeImagesContainer.appendChild(cardDiv);
                    shadesSection.style.display = "block";
                } else {
                    shadeImagesContainer.innerHTML = '<p>No shades found.</p>';
                }
            })
            .catch(error => {
                console.error("Error fetching shades:", error);
                shadeImagesContainer.innerHTML = '<p>Error loading shades.</p>';
            });
    }

    // Capture selected shade image
    document.getElementById("shade-images").addEventListener("click", function (event) {
        let clickedImg = event.target.closest("img");

        if (clickedImg) {
            let shadeId = clickedImg.getAttribute("data-shade-id");
            let imgSrc = clickedImg.getAttribute("src");

            if (shadeId) {
                handleShadeSelection(clickedImg, shadeId, imgSrc);
            }
        }
    });

    function handleShadeSelection(imgElement, shadeId, imgSrc) {
        // Find the specific row using the shade ID
        let parentRow = document.querySelector(`[data-shade-row-id="${shadeId}"]`);

        if (parentRow) {
            //Deselect previously selected image in this row
            parentRow.querySelectorAll(".selected-shade").forEach(img => {
                img.classList.remove("selected-shade");
            });

            //Highlight the clicked image
            imgElement.classList.add("selected-shade");

            //Update hidden input value
            let hiddenInput = parentRow.querySelector(`#hidden_shade_selected_img_${shadeId}`);
            if (hiddenInput) {
                hiddenInput.value = imgSrc;
            }
        }
    }

    // CSS for grid layout and selected image highlight
    let style = document.createElement("style");
    style.innerHTML = `
    .grid-container {
        display: grid;
        grid-template-columns: repeat(5, 1fr); /* 5x5 grid */
        gap: 8px;
    }
    .grid-item {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 120px; /* Set equal height for divs */
    }
    .grid-item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        aspect-ratio: 1 / 1;
        border-radius: 6px;
        border: 1px solid #ccc;
        transition: transform 0.2s ease;
    }
    .grid-item img:hover {
        transform: scale(1.05);
    }
    .selected-shade {
        border: 1px solid #007bff !important;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    }
    .selected-material {
        border: 1px solid #007bff !important;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
    }
`;
    document.head.appendChild(style);



    /** ======= Show Popup with Sub-Images and Navigation ======= **/
    function showSubImagesPopup(subImages) {
        if (subImages.length === 0) {
            alert("No additional images available.");
            return;
        }

        let popupContainer = document.createElement("div");
        popupContainer.id = "subImagesPopup";
        popupContainer.classList.add("popup-container");

        let popupContent = document.createElement("div");
        popupContent.classList.add("popup-content");

        let closeButton = document.createElement("span");
        closeButton.innerHTML = "&times;";
        closeButton.classList.add("close-button");
        closeButton.addEventListener("click", () => document.body.removeChild(popupContainer));

        let leftArrow = document.createElement("span");
        leftArrow.innerHTML = "&#9664;";
        leftArrow.classList.add("left-arrow");

        let rightArrow = document.createElement("span");
        rightArrow.innerHTML = "&#9654;";
        rightArrow.classList.add("right-arrow");

        let imgElement = document.createElement("img");
        imgElement.classList.add("popup-image");

        let videoElement = document.createElement("video");
        videoElement.classList.add("popup-video");
        videoElement.controls = true;

        let currentIndex = 0;

        function updateContent() {
            //Check if it's a video
            if (subImages[currentIndex].endsWith('.mp4')) {
                imgElement.style.display = "none";
                videoElement.src = subImages[currentIndex];
                videoElement.style.display = "block";
            } else {
                videoElement.style.display = "none";
                imgElement.src = subImages[currentIndex];
                imgElement.style.display = "block";
            }
        }
        updateContent();

        leftArrow.addEventListener("click", () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateContent();
            }
        });

        rightArrow.addEventListener("click", () => {
            if (currentIndex < subImages.length - 1) {
                currentIndex++;
                updateContent();
            }
        });

        popupContent.appendChild(closeButton);
        popupContent.appendChild(leftArrow);
        popupContent.appendChild(imgElement);
        popupContent.appendChild(videoElement);
        popupContent.appendChild(rightArrow);
        popupContainer.appendChild(popupContent);
        document.body.appendChild(popupContainer);
    }

    //How did you hear about us?
    const hearDropdown = document.getElementById("how_heard");
    const remarksRow = document.getElementById("remarksRow");
    hearDropdown.addEventListener("change", function() {
        if (this.value === "Others") {
            remarksRow.classList.remove("d-none");
        } else {
            remarksRow.classList.add("d-none");
        }
    });

});