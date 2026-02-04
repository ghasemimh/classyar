<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-6xl mx-auto px-4 py-10">
  <div class="rounded-3xl glass-card overflow-hidden">
    <iframe class="w-full h-[70vh] md:h-[80vh]" style="border:none;" srcdoc='
<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8" />
<title>Advanced Solar System</title>

<script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>

<style>
:root {
  --bg: radial-gradient(circle at center, #10162f, #050711);
}

body {
  margin: 0;
  height: 100vh;
  background: var(--bg);
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: system-ui;
  color: #fff;
}

#system {
  position: relative;
  width: 640px;
  height: 640px;
  filter: drop-shadow(0 0 25px rgba(0,0,0,.6));
}

.sun {
  position: absolute;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: radial-gradient(circle, #fff3a0, #ffb300);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  box-shadow: 0 0 40px #ffb300;
}

.orbit {
  position: absolute;
  top: 50%;
  left: 50%;
  border: 1px dashed rgba(255,255,255,.12);
  border-radius: 50%;
  transform: translate(-50%, -50%);
}

.planet {
  position: absolute;
  top: -8px;
  left: 50%;
  transform: translateX(-50%);
  border-radius: 50%;
  box-shadow: inset -2px -2px 6px rgba(0,0,0,.5);
}

.moon-orbit {
  position: absolute;
  top: 50%;
  left: 50%;
  border: 1px dashed rgba(255,255,255,.2);
  border-radius: 50%;
  transform: translate(-50%, -50%);
}

.moon {
  position: absolute;
  top: -2px;
  left: 50%;
  transform: translateX(-50%);
  border-radius: 50%;
  background: #ddd;
}

/* سیارات */
.p1 { width: 160px; height: 160px; }
.p2 { width: 240px; height: 240px; }
.p3 { width: 320px; height: 320px; }
.p4 { width: 400px; height: 400px; }
.p5 { width: 480px; height: 480px; }

.planet.p1-body { width: 10px; height: 10px; background:#6ec6ff; }
.planet.p2-body { width: 14px; height: 14px; background:#81c784; }
.planet.p3-body { width: 16px; height: 16px; background:#ffb74d; }
.planet.p4-body { width: 18px; height: 18px; background:#e57373; }
.planet.p5-body { width: 20px; height: 20px; background:#ba68c8; }

</style>
</head>

<body>
<div id="system">
  <div class="sun"></div>

  <!-- Planet 1 -->
  <div class="orbit p1">
    <div class="planet p1-body">
      <div class="moon-orbit" style="width:24px;height:24px;">
        <div class="moon" style="width:4px;height:4px;"></div>
      </div>
    </div>
  </div>

  <!-- Planet 2 -->
  <div class="orbit p2">
    <div class="planet p2-body">
      <div class="moon-orbit" style="width:28px;height:28px;">
        <div class="moon" style="width:5px;height:5px;"></div>
      </div>
      <div class="moon-orbit" style="width:36px;height:36px;">
        <div class="moon" style="width:4px;height:4px;"></div>
      </div>
    </div>
  </div>

  <!-- Planet 3 -->
  <div class="orbit p3">
    <div class="planet p3-body">
      <div class="moon-orbit" style="width:32px;height:32px;">
        <div class="moon" style="width:5px;height:5px;"></div>
      </div>
    </div>
  </div>

  <!-- Planet 4 -->
  <div class="orbit p4">
    <div class="planet p4-body">
      <div class="moon-orbit" style="width:34px;height:34px;">
        <div class="moon" style="width:4px;height:4px;"></div>
      </div>
      <div class="moon-orbit" style="width:42px;height:42px;">
        <div class="moon" style="width:5px;height:5px;"></div>
      </div>
    </div>
  </div>

  <!-- Planet 5 -->
  <div class="orbit p5">
    <div class="planet p5-body">
      <div class="moon-orbit" style="width:38px;height:38px;">
        <div class="moon" style="width:4px;height:4px;"></div>
      </div>
    </div>
  </div>
</div>

<script>
  gsap.to(".p1", { rotation: 360, duration: 90, repeat: -1, ease: "linear" });
  gsap.to(".p2", { rotation: -360, duration: 130, repeat: -1, ease: "linear" });
  gsap.to(".p3", { rotation: 360, duration: 180, repeat: -1, ease: "linear" });
  gsap.to(".p4", { rotation: -360, duration: 230, repeat: -1, ease: "linear" });
  gsap.to(".p5", { rotation: 360, duration: 300, repeat: -1, ease: "linear" });

  gsap.to(".moon-orbit", {
    rotation: 360,
    duration: 18,
    repeat: -1,
    ease: "linear"
  });

  document.querySelectorAll(".moon-orbit").forEach(moon => {
  const radius = moon.offsetWidth;
  const speed = radius * 0.4; // قمر دورتر = کندتر (قانون کپلر fake 😄)

  gsap.to(moon, {
    rotation: 360,
    duration: speed,
    repeat: -1,
    ease: "linear"
  });
});

</script>
</body>
</html>'>
    </iframe>
  </div>
</div>
