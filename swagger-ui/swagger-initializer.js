window.onload = function() {
  //<editor-fold desc="Changeable Configuration Block">

  // Especificacion OpenAPI de TodoCamisetas (servida localmente, sin CDN)
  window.ui = SwaggerUIBundle({
    url: "./openapi.yaml",
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout"
  });

  //</editor-fold>
};
