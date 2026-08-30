# Frontend Agent Guide

## Scope

- This guide applies to the Vue/Quasar frontend.
- Prefer the existing frontend `Makefile` and `npm` scripts.
- Follow Quasar and Vue conventions first, and keep implementation consistent with the existing app structure.
- Keep changes aligned with the current codebase instead of introducing new patterns unnecessarily.

## Commands

- Install dependencies: `make install`
- Start dev server: `make dev`
- Fix style/lint issues: `make lint`
- Check style/lint issues: `make lint-check`
- Build production assets: `make build`

## General Frontend Principles

- Prefer Quasar components, utilities, and styling helpers as much as possible.
- Keep UI code simple, readable, and consistent with Quasar patterns.
- Use `script setup` for Vue single-file components.
- Use JavaScript in Vue components and Pinia stores.
- Keep store state, payloads, and API response shapes clear and consistent.
- Keep logic out of components when it belongs in a store or shared utility.
- Use Vue Router conventions and always consider whether a route should be protected/locked.
- Put reusable scripts, app setup, and shared integrations in `src/boot`.

## Folder Conventions

- Components: `src/components`
- Pages: `src/pages`
- Stores: `src/stores`
- Boot files: `src/boot`
- Assets: `src/assets`
- Styles: `src/css`

## Component Rules

- Keep components focused on presentation and user interaction.
- Use stores for all API requests; components should not call APIs directly.
- Prefer Quasar components and props before creating custom UI from scratch.
- Use Quasar utility classes and helpers for spacing, layout, typography, and responsiveness when appropriate.
- Avoid adding CSS inside components.
- Use component-level SCSS only for atomic components or when explicitly required.
- Keep reusable UI pieces in `src/components`.
- Keep page-specific components and page composition straightforward and easy to scan.

## Page Rules

- Store route pages in `src/pages`.
- Keep page components thin and compose them from smaller components and stores.
- Page-level SCSS should live in the dedicated styles folder and stay page-scoped.
- Keep route-based concerns, like loading initial data, in the page or its dedicated store.

## Pinia and API Rules

- All API calls must go through Pinia stores.
- Split stores by domain logic, not by arbitrary UI screens.
- Use JavaScript in stores unless the project is intentionally migrated.
- Keep request payloads, response payloads, state shape, and error handling clear and predictable.
- Keep store actions reusable.
- Centralize API clients and app-wide setup in `src/boot` when appropriate, such as Axios, Pinia plugins, interceptors, or shared configuration.
- Prefer one source of truth per domain to avoid duplicated request logic.

## Styling Rules

- Use SCSS for styling.
- Keep SCSS files in a dedicated styles folder.
- Organize SCSS by purpose:
    - page-level SCSS for pages
    - component-level SCSS for atomic components or when explicitly required
    - layout SCSS for layout-specific structure and spacing
- Create a dedicated layout SCSS file for layout styling.
- Do not place CSS in Vue components.
- Keep colors, fonts, and reusable border values in SCSS variables.
- Keep styling consistent across the app and avoid one-off values when a shared variable makes sense.
- Prefer Quasar styling helpers and utility classes before custom SCSS where practical.

## Routing Rules

- Use Vue Router conventions for route definitions and navigation.
- Keep route guards and auth protection explicit.
- Check whether a route should be public, locked, or role-restricted before adding it.
- Keep route metadata clear and consistent.

## Assets

- Store assets in `src/assets`.
- Split assets by type:
    - `src/assets/icons`
    - `src/assets/images`
    - `src/assets/fonts`
    - other type-based folders as needed
- Keep asset naming clear and predictable.

## Quasar Preferences

- Prefer Quasar layout, form, dialog, table, notification, and input components before custom alternatives.
- Use Quasar props and slots to customize behavior and appearance.
- Prefer Quasar's responsive grid and layout system for page structure.
- Use Quasar utilities and CSS helpers for common spacing and alignment needs.
- Use Quasar icons and built-in helpers where possible.

## Code Quality

- Keep components and stores small and domain-focused.
- Avoid duplication; extract shared logic into stores, composables, boot files, or utilities when it genuinely helps reuse.
- Keep naming consistent with the existing codebase.
- Prefer clear, maintainable code over clever abstractions.
- When adding new work, fit it into the existing frontend structure instead of inventing a new pattern.

## Validation and Safety

- Validate user input before submitting it from the UI when appropriate.
- Handle loading, success, and error states clearly.
- Ensure authenticated flows are guarded appropriately.
- Keep API error handling consistent and user-friendly.

## Testing and Verification
- We will not write tests.
- When frontend behavior changes, verify the affected UI paths manually or through available checks.
- Run `make lint-check` and `make build` after significant frontend changes when possible.
- Keep changes aligned with the existing Quasar/Vue conventions and project structure.
