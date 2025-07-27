# 🍔 La Comanda - API con Sli

Este proyecto es una API desarrollada con Slim, que es un micro-framework de PHP. Trabaja desde el lado del servidor comunicándose con una base de datos SQL.

## 📘 Descripción

La Comanda es una API cuya idea es trabajar la comunicación entre el cliente y el servidor, centrándose en la manipulación de las respuestas (del servidor). Todos los datos persisten en una base de datos SQL, precisamente con el motor MySQL. Además, cuenta con un sistema de logeo mediante JSON Web Token. La temática es la de un restaurante, donde se pueden crear distintos tipos de usuarios, hacer pedidos, prepararlos y cobrarlos.

## 👨‍🚀 Consultas en Postman

Así se ve la colección de consultas realizadas en Postman (archivo importable en este repositorio)

<img src="https://github.com/user-attachments/assets/cbb1d075-d449-48e1-a0fa-4e04caff3740"/>

## 🛠️ Consultas posibles

- **GET**: Permite traer todos (o individualmente) las distintas entidades del sistema.
- **POST**: Permite hacer altas de distintas entidades, mesas, productos, pedidos, y realizar acciones de pedidos y mesas.
- **PUT**: Permite hacer modificaciones de algunos tipos de usuario.
- **DELETE**: Permite hacer bajas los distintos tipos de entidades.
- **Carga/Descarga CSV**: Con los verbos **GET** y **POST** se pueden realizar importaciones o exportaciones de datos de usuarios mediante archivos CSV.

## 🚀 Cómo Ejecutar el Proyecto

1. **Clonar el repositorio**:

    Clonar el repositorio con el siguiente comando, o descargar el ZIP.
   
    ```bash
    git clone https://github.com/miguecode/la-comanda-api.git

2. **Instalar dependencias**:

    Hay que tener Composter instalado, y después ejecutar este comando:

    ```bash
    composer install
    ```

3. **Configurar la base de datos [ADVERTENCIA]**

    - Hay que tener MySQL instalado.
    - Hay que tener una base de datos con la estructura específica que necesita el proyecto.

    ⚠️ **La advertencia cae en este último punto. El script para crearla no lo tengo por ahora...**

    - Editar el archivo .env con las credenciales de tu base de datos

      ```bash
      MYSQL_HOST=localhost
      MYSQL_PORT=3306
      MYSQL_USER=tu_usuario
      MYSQL_PASS=tu_contraseña
      MYSQL_DB=la_comanda
      ```

4. **Levantar el servidor**:

    Para correr el servidor PHP Slim en el puerto hay que ejecutar el comando:

      ```bash
      php -S localhost:666 -t app
      ```

5. **Importar las consultas de Postman**:

    El repositorio contiene un archivo .json llamado "La Comanda.postman_collection.json" (dentro de la carpeta 'postman'). Este archivo es el que hay que importar en Postman.


## 📌 Aclaraciones

- Fue creado en 2023, mientras cursaba la carrera de Tecnicatura Universitaria en Programación, en la Universidad Tecnológica Nacional.
- El proyecto está bajo la licencia MIT.

## 🗃️ Otros proyectos similares
- [El Hotel - API con Slim](https://github.com/miguecode/slim-hotel-api)
