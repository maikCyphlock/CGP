# Proyecto Contraloría Municipio Páez

¡Bienvenido al proyecto! Este documento te ayudará a entender cómo está organizado el código y por qué hemos elegido esta estructura.

## 📂 Estructura del Proyecto

El proyecto sigue una estructura estándar para aplicaciones web estáticas, lo que facilita el mantenimiento y la escalabilidad.

```text
/
├── index.html              # Página principal (antes contraloria.html)
├── pages/                  # Otras páginas HTML (ej. apartadodedenuncia.html)
└── assets/                 # Recursos estáticos
    ├── css/                # Hojas de estilo (CSS)
    ├── img/                # Imágenes (Logos, fotos, etc.)
    └── js/                 # Archivos de lógica y animaciones (JavaScript)
```

## 🛠️ ¿Por qué esta organización?

Como desarrollador junior, es importante entender los beneficios de organizar bien tus archivos:

1.  **Separación de Responsabilidades**: Al separar el contenido (HTML), el diseño (CSS) y la lógica (JS), el código es más fácil de leer y depurar.
2.  **Escalabilidad**: Si el proyecto crece (más imágenes, más páginas), no tendrás un desorden de archivos en la raíz. Sabrás exactamente dónde poner cada cosa nueva.
3.  **Estándares de la Industria**: La mayoría de las empresas y proyectos profesionales usan una estructura similar (especialmente la carpeta `assets/`). Aprender esto ahora te preparará para proyectos más grandes.
4.  **Mantenimiento**: Si necesitas cambiar un estilo, vas a `assets/css/`. Si necesitas cambiar una imagen, vas a `assets/img/`. No hay pérdida.

## 📝 Notas para Desarrolladores

### 1. Referencias de Archivos
Debido a que hemos movido los archivos a carpetas, las rutas han cambiado:
- Desde la raíz (`index.html`), las rutas son directas: `assets/css/archivo.css`.
- Desde las páginas internas (`pages/`), debes subir un nivel: `../assets/css/archivo.css`.

### 2. Imágenes
Todas las imágenes institucionales se encuentran en `assets/img/`. Si vas a añadir una nueva, asegúrate de que sea en formato `.webp` o `.jpeg` optimizado para web. esto es opcional

### 3. Estilos
- `contraloria.css`: Estilos generales de la institución y la página de inicio.
- `denuncias.css`: Estilos específicos para el módulo de denuncias y el wizard de pasos.

### 4. JavaScript
- `assets/js/animations.js`: Espacio reservado para efectos visuales y lógica de interacción global.

## 🚀 Próximos Pasos
Si quieres añadir una nueva página:
1. Crea el archivo `.html` dentro de la carpeta `pages/`.
2. Asegúrate de que los links al CSS y las imágenes usen `../assets/...`.
3. ¡Añade un link a tu nueva página en el menú de navegación de `index.html`!

---
*Desarrollado con ❤️ para la Contraloría del Municipio Páez.*
