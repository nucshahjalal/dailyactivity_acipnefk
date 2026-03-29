<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Map Slider</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        .carousel,
        .carousel-inner,
        .carousel-item {
            height: 100vh;
        }

        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }

        /* make arrows more visible on map */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: invert(1);
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>

<body>

    <div id="pageSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">

            <div class="carousel-item active">
                <iframe src="{{ route('today') }}"></iframe>
            </div>

            <div class="carousel-item">
                <iframe src="{{ route('yesterday') }}"></iframe>
            </div>

            <div class="carousel-item">
                <iframe src="{{ route('monthly') }}"></iframe>
            </div>

            <div class="carousel-item">
                <iframe src="{{ route('last_three_month') }}"></iframe>
            </div>

        </div>

        <!-- PREV / NEXT -->
        <button class="carousel-control-prev" type="button" data-bs-target="#pageSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#pageSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh the whole page after 5 minutes (300000 ms)
        setTimeout(function() {
            location.reload();
        }, 300000);

        // Bootstrap carousel auto-slide every 5 seconds
        // const carouselElement = document.querySelector('#pageSlider');
        // const carousel = new bootstrap.Carousel(carouselElement, {
        //     interval: 5000,
        //     ride: 'carousel'
        // });
    </script>
</body>

</html>
