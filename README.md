Join our community Discord https://discord.gg/GNg3JUX
Find our website https://twohoursonelife.com


## Development Setup

We use `mise` to manage development tools and `just` to run tasks.

1. Install tools via `mise`:
   ```bash
   mise install
   ```

2. Run the environment setup (starts containers, creates directories, and initializes databases):
   ```bash
   just setup
   ```

3. Manage the environment:
   - `just dev`: Start containers
   - `just down`: Stop containers
   - `just logs`: Tail container logs
   - `just db-shell`: Open MySQL shell

## To do
- Remove web directory and move public directory up one
- Move config.php out of public directory, into root