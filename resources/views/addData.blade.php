{{-- <!DOCTYPE html>
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
    <h1 class="text-center">Add New Data</h1>

    <div class="row">
        <div class="col-md-6">
            <label for="nim">NIM:</label>
            <input type="text" id="nim" placeholder="Enter your NIM" class="form-control mb-3" maxlength="10">

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
    const maxCaptures = 100;
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
        const nim = document.getElementById('nim').value;
        if (isCapturing || !nim) {
            alert('Please enter your NIM before taking snapshots.');
            return;
        }

        if (nim.length > 10) {
            alert('NIM cannot exceed 10 characters.');
            return;
        }

        isCapturing = true;
        captureCount = 0;
        images.length = 0;
        document.getElementById('results').innerHTML = '';
        captureNextImage(nim);
    }

    function captureNextImage(nim) {
        if (captureCount >= maxCaptures) {
            isCapturing = false;
            sendImagesToServer(nim);
            return;
        }

        Webcam.snap(function(data_uri) {
            images.push(data_uri);

            const imgElement = document.createElement('img');
            imgElement.src = data_uri;
            imgElement.style = "width:100px; margin: 5px;";
            document.getElementById('results').appendChild(imgElement);

            captureCount++;
            setTimeout(() => captureNextImage(nim), frameInterval);
        });
    }

    function sendImagesToServer(nim) {
        fetch("{{ route('webcam.capture') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ images: images, id: nim })
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
</html> --}}

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
    <h1 class="text-center">Add New Data</h1>

    <div class="row">
        <div class="col-md-6">
            <label for="nim">NIM:</label>
            <input type="text" id="nim" placeholder="Enter your nim" class="form-control mb-3">

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
    const maxCaptures = 100;
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
        const nim = document.getElementById('nim').value;
        if (isCapturing || !nim) {
            alert('Please enter a nim before taking snapshots.');
            return;
        }

        isCapturing = true;
        captureCount = 0;
        images.length = 0;
        document.getElementById('results').innerHTML = '';
        captureNextImage(nim);
    }

    function captureNextImage(nim) {
        if (captureCount >= maxCaptures) {
            isCapturing = false;
            sendImagesToServer(nim);
            return;
        }

        Webcam.snap(function(data_uri) {
            images.push(data_uri);

            const imgElement = document.createElement('img');
            imgElement.src = data_uri;
            imgElement.style = "width:100px; margin: 5px;";
            document.getElementById('results').appendChild(imgElement);

            captureCount++;
            setTimeout(() => captureNextImage(nim), frameInterval);
        });
    }

    function sendImagesToServer(nim) {
        fetch("{{ route('webcam.capture') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ images: images, nim: nim })
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
