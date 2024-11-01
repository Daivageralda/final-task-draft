<!DOCTYPE html>
<html>
<head>
    <title>Smart Presences</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.25/webcam.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <style type="text/css">
        #results { padding:20px; border:1px solid; background:#ccc; }
        #framerate { font-size: 16px; color: green; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center">Presence</h1>

    <div class="row">
        <div class="col-md-6">

            <div id="my_camera"></div>
            <br/>
            <input type="button" value="Take Snapshot" onClick="captureImages()">
            <p>Frame Rate: <span id="framerate">0</span> FPS</p>
        </div>
        <div class="col-md-6">
            <div id="results">Your captured images will appear here...</div>
        </div>
        <div class="col-md-12 text-center">
            <span id="status"></span>
        </div>
    </div>
</div>

<script>
    let lastFrameTime = Date.now();
    let frameInterval = 1000 / 30;
    let isCapturing = false;
    let captureCount = 0;
    const maxCaptures = 50;
    const images = [];

    Webcam.set({
        width: 490,
        height: 350,
        image_format: 'jpeg',
        jpeg_quality: 120
    });

    Webcam.attach('#my_camera');

    function updateFrameRate() {
        const currentFrameTime = Date.now();
        const elapsed = (currentFrameTime - lastFrameTime) / 1000;
        const fps = (1 / elapsed).toFixed(2);
        document.getElementById('framerate').innerText = fps;
        lastFrameTime = currentFrameTime;

        frameInterval = 1000 / fps;

        requestAnimationFrame(updateFrameRate);
    }

    function captureImages() {

        isCapturing = true;
        captureCount = 0;
        images.length = 0;
        document.getElementById('results').innerHTML = '';
        captureNextImage();
    }

    function captureNextImage() {
        if (captureCount >= maxCaptures) {
            isCapturing = false;
            sendImagesToServer();
            return;
        }

        Webcam.snap(function(data_uri) {
            images.push(data_uri);

            const imgElement = document.createElement('img');
            imgElement.src = data_uri;
            imgElement.style = "width:100px; margin: 5px;";
            document.getElementById('results').appendChild(imgElement);

            captureCount++;
            setTimeout(() => captureNextImage(), frameInterval);
        });
    }

    function sendImagesToServer() {
        fetch("{{ route('presence.capture') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ images: images})
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('status').innerText = data.message;
            console.log('Uploaded URLs:', data.image_urls);
        })
        .catch(error => console.error('Error uploading images:', error));
    }

    updateFrameRate();
</script>

</body>
</html>
