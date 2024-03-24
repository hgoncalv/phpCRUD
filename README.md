# CRUD API with PHP mySQL

This project consists of a CRUD (Create, Read, Update, Delete) API developed in PHP and mySQL, deployed on Docker, to later be integrated with a React Native application. It allows for seamless interaction between the backend and frontend components, enabling data manipulation operations.

## Setup Instructions

1. Clone the repository.
2. Ensure you have Docker installed on your system.
3. Navigate to the project directory.
4. Run the following command to start the Docker containers:

```bash
docker-compose up -d
```

5. Once the containers are running, Docker will automatically create a MySQL database container. You can access the API at [http://localhost:8080](http://localhost:8080), where `8080` is the port configured for the PHP Apache server.

## API Endpoints

- **GET /endpoint**: Retrieve data.
- **POST /endpoint**: Create new data.
- **PUT /endpoint**: Update existing data.
- **DELETE /endpoint**: Delete data.

## Usage

To interact with the API, you can use tools like Postman or curl commands. Here's an example of how to make a GET request to retrieve data:

```bash
curl -X GET http://localhost:8080/endpoint
```

Replace `/endpoint` with the desired endpoint.

## Project Structure

- `basePath.php`: Configuration file for defining base paths.
- `functions/`: Directory containing various utility functions.
- `classes/`: Directory containing PHP classes for database interaction and CRUD operations.

## Contributors

- [hgoncalv](https://github.com/hgoncalv)

## License

This project is licensed under the [MIT License](LICENSE).
