# UI Refactor Plan: Reducing Information Overload

## Goal
Refactor the Laravel application's UI to reduce visual noise, reclaim vertical space, and improve the user experience by moving from "all-at-once" displays to "on-demand" and "tabbed" interfaces.

## 1. Generic Table Component Refactor (`resources/views/components/tabela.blade.php`)

### Problem
The current component renders every column in the database, leading to horizontal scrolling and cognitive overload. Search and filters take up significant vertical space.

### Proposed Changes
- **Defined Column Approach**: 
    - Introduce a `@props(['columns' => []])` for the component.
    - If `columns` is provided, only render those specific columns in the main table view.
    - If not provided, fallback to a sensible default or the current "all columns" behavior (but marked as "legacy").
- **Side Drawer for Full Details**:
    - Implement an Alpine.js managed side panel (Right Drawer).
    - When the "View" (eye icon) action is clicked, instead of just updating a variable, slide in a panel that displays *all* columns for that specific record in a vertical key-value list.
    - This allows the main table to be lean while keeping full data accessible.
- **Layout Optimization**:
    - Move the search and filter buttons into a more compact header row.
    - Combine the search input and action buttons into a single flex-container that minimizes padding.
- **Row Action Refinement**:
    - Shift from individual buttons to a single "Actions" dropdown or a more subtle hover-triggered menu to reduce visual noise in every row.

## 2. Certificates Page Refactor (`resources/views/certificados/index.blade.php`)

### Problem
A 2-column grid forces the user to see both the status list and the upload form simultaneously, creating a cluttered interface. The database selector is integrated into the header but doesn't feel like a top-level context switch.

### Proposed Changes
- **Tabbed Interface**:
    - Replace the `grid-cols-2` layout with an Alpine.js tab system.
    - **Tab 1: [Certificados Ativos]** - Contains the current list of certificates.
    - **Tab 2: [Enviar Novo]** - Contains the upload form.
- **Card Layout for Status**:
    - Transform the list items into structured cards with:
        - High-visibility status badges (e.g., Green for "Arquivo Presente", Red for "Ausente").
        - Clearer visual separation between "Titular" and "Procurador".
        -- Compact action buttons (Download/Delete) aligned to the right.
- **Improved Visual Hierarchy**:
    - Move the Database Selection (`$bancosDisponiveis`) to a dedicated "Context Bar" at the very top, separate from the page header, making it clear that this affects the entire view.

## Implementation Strategy

### Phase 1: `tabela.blade.php` Core Logic
1. Update `@props` to include `definedColumns`.
2. Wrap the table header and body in a conditional check for `definedColumns`.
3. Add the Side Drawer HTML/CSS using Tailwind transition classes and `x-show`.

### Phase 2: `tabela.blade.php` UI Polish
1. Refactor the top toolbar for vertical space.
2. Implement the new row action pattern.

### Phase 3: `certificados/index.blade.php` Structural Shift
1. Wrap the main content in an Alpine.js state `x-data="{ tab: 'list' }"`.
2. Create the tab navigation buttons.
3. Move the upload form and list into `x-show="tab === '...'"` containers.
4. Redesign the certificate items as Cards.
5. Extract the DB selector into a top-level context bar.

## Critical Files for Implementation
- `resources/views/components/tabela.blade.php`
- `resources/views/certificados/index.blade.php`
