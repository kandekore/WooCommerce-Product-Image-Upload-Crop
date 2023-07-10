var j = jQuery.noConflict();

j(document).ready(function() {
    var input = j('#custom_image')[0];
    var image = j('#preview_image')[0];
    var cropButton = j('#crop_image')[0];
    var cropper;

    input.addEventListener('change', function (e) {
        var file = e.target.files[0];

        if (file) {
            var reader = new FileReader();

            reader.onload = function (e) {
                image.src = e.target.result;
                image.style.display = 'block';
                cropButton.style.display = 'block';

                // Use the first DOM element from jQuery's array-like object
                cropper = new Cropper(image, {
                    aspectRatio: 1
                });
            }

            reader.readAsDataURL(file);
        }
    });

    cropButton.addEventListener('click', function () {
        image.src = cropper.getCroppedCanvas().toDataURL('image/jpeg');
        cropButton.style.display = 'none';
        cropper.destroy();
    });
});
