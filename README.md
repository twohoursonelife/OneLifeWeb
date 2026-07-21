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

## Testing
Once you have the local dev stack running (`just dev`), you can run some simple
integration tests against it with `just test`. Or use `just test [hostname]` to hit any
remote deployment.

## Deployment
`just deploy` will ship it over to Fly.io.

