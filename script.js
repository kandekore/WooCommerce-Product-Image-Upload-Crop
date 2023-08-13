var j = jQuery.noConflict();

j(document).ready(function() {
    var input = j('#custom_image')[0];
    var container = j('#crop-container')[0];
    var cropButton = j('#crop_image')[0];
      var imageUpload = j('.image-upload')[0];
    var cropType = j(imageUpload).data('crop-type');
    var croppie;

    input.addEventListener('change', function (e) {
        var file = e.target.files[0];

        if (file) {
            var reader = new FileReader();

            reader.onload = function (e) {
                // create an image and add it to the container
                var img = document.createElement("img");
                img.id = "image-to-crop";
                img.src = e.target.result;
                container.innerHTML = ""; // remove previous image if it exists
                container.appendChild(img);
                container.style.display = 'block';
                cropButton.style.display = 'block';

                // initialize croppie on the new image
                croppie = new Croppie(img, {
                       enableExif: true,
                    viewport: { width: 400, height: 400, type: cropType },
                    boundary: { width: 500, height: 500 },
					enableResize: false,
    				enableOrientation: true,
    				mouseWheelZoom: 'ctrl'   
                    
                    
                });
            }

            reader.readAsDataURL(file);
        }
    });

    cropButton.addEventListener('click', function () {
        croppie.result('canvas').then(function(croppedImg) {
            var img = document.querySelector('#image-to-crop');
            img.src = croppedImg;
            cropButton.style.display = 'none';

            j.ajax({
                type: 'POST',
                url: '/wp-admin/admin-ajax.php',
                data: { 
                    'action': 'upload_image',
                    'image': croppedImg
                },
                success: function(response) {
console.log("Image uploaded successfully:", response);                }
            });
        });

        croppie.destroy();
    });
});
