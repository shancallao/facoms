<!-- File: public/assets/js/qr-scanner.js -->
<!-- Uses getUserMedia and jsQR (include jsQR from CDN) -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<video id="qr-video" autoplay playsinline style="width:100%;max-width:600px"></video>
<canvas id="qr-canvas" style="display:none"></canvas>
<script>
(async function(){
  const video = document.getElementById('qr-video');
  const canvas = document.getElementById('qr-canvas');
  const ctx = canvas.getContext('2d');

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
    video.srcObject = stream;
    video.setAttribute("playsinline", true);
    await video.play();
    requestAnimationFrame(tick);
  } catch (e) {
    console.error('Camera error', e);
    alert('Camera not accessible');
  }

  function tick(){
    if(video.readyState === video.HAVE_ENOUGH_DATA){
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      ctx.drawImage(video,0,0,canvas.width,canvas.height);
      const imageData = ctx.getImageData(0,0,canvas.width,canvas.height);
      const code = jsQR(imageData.data, imageData.width, imageData.height);
      if(code){
        // stop camera
        const tracks = video.srcObject.getTracks();
        tracks.forEach(t=>t.stop());
        handleQRCode(code.data);
        return;
      }
    }
    requestAnimationFrame(tick);
  }

  async function handleQRCode(qrValue){
    // POST to API
    const facultyId = window.FACULTY_ID || null; // set from server-side rendered template
    const res = await fetch('/api/qr.php?action=scan', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ qr_value: qrValue, faculty_id: facultyId })
    });
    const json = await res.json();
    if(json.status === 'ok'){
      alert(json.message);
      // optionally refresh schedule view
      location.reload();
    } else {
      alert('QR Error: ' + json.message);
    }
  }
})();
</script>
