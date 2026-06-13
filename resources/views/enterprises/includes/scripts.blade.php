<script>
  (function(d,t) {
    var BASE_URL="https://chat.inoqualab.com";
    var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=BASE_URL+"/packs/js/sdk.js";
    g.async = true;
    s.parentNode.insertBefore(g,s);
    g.onload=function(){
      window.chatwootSDK.run({
        websiteToken: 'Sn8sdgZ4toBBXcamEKoG5rco',
        baseUrl: BASE_URL
      })
    }
  })(document,"script");
</script>
