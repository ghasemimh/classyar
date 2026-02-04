<footer class="bg-white/70 text-slate-600 text-center py-5 text-sm border-t border-white/60 backdrop-blur">
    تمامی حقوق محفوظ است © <?= date('Y') ?> | <?= $CFG->sitename ?>
</footer>

<div class="loader-wrapper">
    <span class="loader"><span class="loader-inner"></span></span>
</div>
<script>
    $(document).ready(function(){
        $(".loader-wrapper").fadeOut("slow");
    });
</script>
<style>

.loader-wrapper {
    width: 100%;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 10000;
    background-color: #f8f5f0;
    display: flex;
    justify-content: center;
    align-items: center;
}

.loader {
  display: inline-block;
  width: 30px;
  height: 30px;
  position: relative;
  border: 4px solid #0f766e;
  animation: loader 2s infinite ease;
}

.loader-inner {
  vertical-align: top;
  display: inline-block;
  width: 100%;
  background-color: #0f766e;
  animation: loader-inner 2s infinite ease-in;
}

@keyframes loader {
  0% {
    transform: rotate(0deg);
  }
  
  25% {
    transform: rotate(180deg);
  }
  
  50% {
    transform: rotate(180deg);
  }
  
  75% {
    transform: rotate(360deg);
  }
  
  100% {
    transform: rotate(360deg);
  }
}

@keyframes loader-inner {
  0% {
    height: 0%;
  }
  
  25% {
    height: 0%;
  }
  
  50% {
    height: 100%;
  }
  
  75% {
    height: 100%;
  }
  
  100% {
    height: 0%;
  }
}

    </style>
</body>
</html>
