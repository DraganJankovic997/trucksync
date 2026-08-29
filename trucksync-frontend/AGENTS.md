# Frontend Agent Guide

## Scope

- Quasar/Vue frontend lives in this directory.
- Prefer `npm` scripts and this directory's `Makefile`.
- Keep the UI simple and consistent with Quasar conventions.

## Commands

- Install dependencies: `make install`
- Start dev server: `make dev`
- Fix style/lint issues: `make lint`
- Check style/lint issues: `make lint-check`
- Build production assets: `make build`

## Rules

- Use existing Quasar, Vue, Vue Router, and Pinia patterns.
- Keep components focused and easy to scan.
- Run `make lint-check` and `make build` after frontend changes when possible.
