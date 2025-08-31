/*
 * JavaScript helper functions for ASD Detection App
 *
 * This script provides utilities to preview images before upload,
 * perform asynchronous image detection via Fetch API, and handle
 * UI updates on detection pages.
 */

/**
 * Displays a preview of the selected image in the detection form.
 * The preview element must contain an <img> element for display.
 *
 * @param {HTMLInputElement} input The file input element
 */
function previewImage(input) {
    const previewContainer = document.getElementById('preview');
    const img = previewContainer.querySelector('img');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Global variable to hold camera stream
let cameraStream = null;

/**
 * Opens the device camera and displays the video feed. When invoked,
 * it requests permission to access the camera. If permission is
 * granted, the video element will display the camera feed and the
 * capture controls will appear. On mobile devices this will
 * typically activate the rear camera.
 */
function openCamera() {
    const cameraContainer = document.getElementById('camera-container');
    const video = document.getElementById('camera-video');
    navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
        cameraStream = stream;
        video.srcObject = stream;
        video.play();
        cameraContainer.style.display = 'block';
    }).catch(err => {
        alert('Unable to access camera: ' + err);
    });
}

/**
 * Stops the camera stream and hides the camera interface.
 */
function closeCamera() {
    const cameraContainer = document.getElementById('camera-container');
    const video = document.getElementById('camera-video');
    if (cameraStream) {
        const tracks = cameraStream.getTracks();
        tracks.forEach(track => track.stop());
        cameraStream = null;
    }
    video.srcObject = null;
    cameraContainer.style.display = 'none';
}

/**
 * Captures a frame from the video stream and sends it for detection.
 * The captured image is drawn onto a hidden canvas, converted to a
 * Blob, and then submitted to the detection endpoint as if it were
 * selected via the file input. The preview will show the captured
 * image.
 */
function takePhoto() {
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    const context = canvas.getContext('2d');
    // Draw the current frame onto the canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    // Stop the camera
    closeCamera();
    // Convert canvas to data URL
    canvas.toBlob(function(blob) {
        // Create a File object from the blob
        const file = new File([blob], 'capture.png', { type: 'image/png' });
        // Show preview
        const previewContainer = document.getElementById('preview');
        const img = previewContainer.querySelector('img');
        img.src = URL.createObjectURL(blob);
        previewContainer.style.display = 'block';
        // Submit captured image
        submitCapturedImage(file);
    }, 'image/png');
}

/**
 * Submits a captured image file via Fetch API for detection. The
 * result will be displayed in the detection-result container.
 *
 * @param {File} file The captured image file
 */
function submitCapturedImage(file) {
    const resultContainer = document.getElementById('detection-result');
    resultContainer.innerHTML = 'Processing...';
    const formData = new FormData();
    formData.append('face', file);
    fetch('detect.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json()).then(data => {
        if (data.status === 'ready') {
            resultContainer.innerHTML =
                `<p><strong>Result:</strong> ${data.result}</p>` +
                `<p><strong>Probability:</strong> ${(data.probability * 100).toFixed(1)}%</p>`;
        } else if (data.status === 'pending') {
            resultContainer.innerHTML = `<p>${data.message}</p>`;
        } else {
            resultContainer.innerHTML = '<p class="error">' + (data.message || 'Unknown error') + '</p>';
        }
    }).catch(err => {
        resultContainer.innerHTML = '<p class="error">An error occurred: ' + err + '</p>';
    });
}

/**
 * Sends the image to the server for ASD detection via AJAX. On
 * success it displays the returned probability and result in the
 * result container. The form must include the file input and a
 * CSRF token if used.
 *
 * @param {Event} event The form submission event
 */
async function submitDetection(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const resultContainer = document.getElementById('detection-result');
    resultContainer.innerHTML = 'Processing...';
    try {
        const response = await fetch('detect.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.status === 'ready') {
            // Both image and questionnaire have been provided; show result
            resultContainer.innerHTML =
                `<p><strong>Result:</strong> ${data.result}</p>` +
                `<p><strong>Probability:</strong> ${(data.probability * 100).toFixed(1)}%</p>`;
        } else if (data.status === 'pending') {
            // Only image provided; ask user to complete questionnaire
            resultContainer.innerHTML = `<p>${data.message}</p>`;
        } else {
            resultContainer.innerHTML = '<p class="error">' + (data.message || 'Unknown error') + '</p>';
        }
    } catch (error) {
        resultContainer.innerHTML = '<p class="error">An error occurred: ' + error + '</p>';
    }
}