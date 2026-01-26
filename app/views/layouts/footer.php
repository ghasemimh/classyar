<footer class="bg-gray-100 text-gray-600 text-center py-4 text-sm border-t border-gray-200">
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

body {
  background-color: #242F3F;
}
.loader-wrapper {
    width: 100%;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 10000;
    background-color: #242F3F;
    display: flex;
    justify-content: center;
    align-items: center;
}

.loader {
  display: inline-block;
  width: 30px;
  height: 30px;
  position: relative;
  border: 4px solid #Fff;
  animation: loader 2s infinite ease;
}

.loader-inner {
  vertical-align: top;
  display: inline-block;
  width: 100%;
  background-color: #fff;
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