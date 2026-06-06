default:
    @just --list

# Start the development environment
dev:
    docker compose up -d
    @echo "Development server running at http://localhost"
    docker compose logs -f

# Stop the development environment
down:
    docker compose down

# Restart the development environment
restart: down dev

# Run initial setup and create database tables
setup:
    docker compose up -d
    mkdir -p web/public/photoServer/photos
    mkdir -p data/diffDownloads/patches
    just sync-faces
    @echo "Waiting for database to be ready..."
    @until docker compose exec -T mysql mysqladmin ping -h 127.0.0.1 -uusername -ppassword --silent >/dev/null 2>&1; do sleep 1; done
    @echo "Initializing database tables..."
    @curl -s "http://localhost/ticketServer/server.php?action=ts_setup" >/dev/null 2>&1 || true
    @curl -s "http://localhost/curseServer/server.php?action=cs_setup" >/dev/null 2>&1 || true
    @curl -s "http://localhost/fitnessServer/server.php?action=fs_setup" >/dev/null 2>&1 || true
    @curl -s "http://localhost/lifeTokenServer/server.php?action=lt_setup" >/dev/null 2>&1 || true
    @curl -s "http://localhost/lineageServer/server.php?action=ls_setup" >/dev/null 2>&1 || true
    @curl -s "http://localhost/photoServer/server.php?action=ps_setup" >/dev/null 2>&1 || true
    @curl -s "http://localhost/reviewServer/server.php?action=rs_setup" >/dev/null 2>&1 || true
    @echo "Database setup completed successfully!"

# Show logs from the containers
logs:
    docker compose logs -f

# Access the MySQL database shell
db-shell:
    docker compose exec -it mysql mysql -uusername -ppassword twohoursonelife

# Reset the database (clears all data) and run setup again
db-reset:
    docker compose down -v
    just setup

# Copy face images from the OneLifeData7 repository
sync-faces:
    mkdir -p web/public/lineageServer/faces
    cp -n ../OneLifeData7/faces/*.png web/public/lineageServer/faces/

# Run tests
test base_url="http://localhost":
    TEST_BASE_URL="{{base_url}}" uv run tests/test_endpoints.py

