# Repository Guidelines

## Project Structure & Module Organization

This repository is currently a starting workspace: no application source, tests, assets, or build configuration are present. The root contains environment directories (`.agents/`, `.codex/`, `.aws/`, and `.git/`); treat these as environment metadata rather than application modules.

When adding project code, use a clear layout such as `src/` for implementation, `tests/` for automated tests, and `assets/` for static resources, where appropriate. Document the actual layout in a root `README.md` as the project develops.

## Build, Test, and Development Commands

No build, test, or local development commands are configured yet. Do not assume commands such as `npm test` or `make build` work.

When introducing a toolchain, include its manifest and configuration, and document exact setup, development, build, and test commands in `README.md`. Prefer reproducible commands that contributors can run from the repository root.

## Coding Style & Naming Conventions

No language, formatter, or linter has been established. Follow the conventions of the chosen language and keep formatting consistent within each file. Use descriptive names for files, functions, and modules; avoid unexplained abbreviations.

Add shared formatter or linter configuration with the first implementation when useful. Keep changes focused and avoid unrelated formatting edits.

## Testing Guidelines

No testing framework or coverage threshold is configured. Add tests alongside new behavior using the selected language’s standard framework. Use descriptive test names that identify the behavior and expected result. Cover important edge cases and document how to run the suite.

## Commit & Pull Request Guidelines

Git history is unavailable in this workspace, so no existing commit convention can be verified. Use concise, imperative commit subjects, such as `Add project setup instructions`.

Pull requests should explain the change, its purpose, and validation performed. Link related issues when available; include screenshots for visible interface changes. Explicitly note any checks that could not be run.

## Security & Configuration

Never commit credentials, tokens, or local secrets. Use placeholder values in configuration examples and document required environment variables without exposing their values.
