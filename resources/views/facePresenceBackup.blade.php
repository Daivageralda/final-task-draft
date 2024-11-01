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
            <p>Name: <span id="name">Unknown</span></p>

            <div id="my_camera"></div>
            <br/>
            <p>Frame Rate: <span id="framerate">0</span> FPS</p>
        </div>
        <div class="col-md-6">
        </div>
        <div class="col-md-12 text-center">
            <span id="status"></span>
        </div>
    </div>
</div>

<script>
    let lastFrameTime = Date.now();
    let frameInterval = 1000 / 5; // Adjust FPS capture rate
    let captureCount = 0;

    Webcam.set({
        width: 490,
        height: 350,
        image_format: 'jpeg',
        jpeg_quality: 90
    });

    Webcam.attach('#my_camera');

    function captureAndSendImage() {
        Webcam.snap(function(data_uri) {
            // Send image to the server
            fetch("{{ route('presence.capture') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ image: data_uri })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('name').innerText = data.name || 'Unknown';
                } else {
                    console.error('Face recognition failed');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    // Automatically capture and send images every few seconds
    setInterval(captureAndSendImage, frameInterval);
</script>


</body>
</html>
