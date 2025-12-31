# Architecture Documentation

## Project Overview

This is a **static educational website** hosting interactive science simulations for teaching physics, chemistry, biology, integrated science, and astronomy. The project is designed as a collection of standalone HTML files, each implementing a specific simulation or concept visualization. The site is optimized for GitHub Pages deployment and requires no build process.

**Primary Purpose**: Educational resource for HKDSE (Hong Kong Diploma of Secondary Education) and secondary school science curriculum.

**Repository**: `qos-lkb.github.io` (GitHub Pages)

---

## Technology Stack

### Core Technologies

- **HTML5**: Semantic markup for all simulation pages
- **CSS3**: Custom styling with Tailwind CSS utility classes
- **Vanilla JavaScript**: No frameworks required (some simulations use React standalone)
- **Static Site**: No server-side processing or build step

### External Dependencies (CDN)

#### UI & Styling
- **Tailwind CSS** (`cdn.tailwindcss.com`)
  - Utility-first CSS framework
  - Used for responsive layouts, color schemes, and component styling
  - No build process required (CDN version)

#### Visualization Libraries
- **Three.js** (`cdn.jsdelivr.net/npm/three@*`)
  - 3D graphics library for particle systems, 3D models, and interactive visualizations
  - Versions used: r128, 0.154.0, 0.160.0 (varies by simulation)
  - **OrbitControls**: Camera controls for 3D scenes
  - ES modules via import maps in newer simulations

- **Chart.js** (`cdn.jsdelivr.net/npm/chart.js`)
  - Data visualization for graphs, charts, and real-time data plotting
  - Used for displaying relationships (P-V, V-T, P-T curves, etc.)

#### Mathematical Rendering
- **MathJax 3** (`cdn.jsdelivr.net/npm/mathjax@3`)
  - LaTeX/MathML rendering for mathematical equations
  - Supports inline (`$...$`) and display (`$$...$$`) math
  - Async loading for performance

#### Optional Frameworks (Select Simulations)
- **React 18** (`unpkg.com/react@18`)
  - Used in some advanced simulations (e.g., Wave Interference)
  - Standalone version with Babel for JSX compilation
  - No build step required

- **Babel Standalone** (`unpkg.com/@babel/standalone`)
  - JSX/ES6+ transpilation in browser
  - Only used where React is present

---

## Project Structure

```
qos-lkb.github.io/
├── index.html                 # Main landing page (English, tab-based navigation)
├── index_new.html             # Modern landing page (Chinese/English, sidebar navigation)
├── architecture.md            # This file
├── link.txt                   # External links reference
│
├── physics/                   # Physics simulations (HKDSE curriculum)
│   ├── 01/                    # Unit 1: Heat and Gases
│   │   ├── 0101_calibration_of_thermometer.html
│   │   ├── 0101_clinical_thermometer.html
│   │   ├── 0102_conduction.html
│   │   ├── 0102_convection.html
│   │   ├── 0102_radiation_and_colour.html
│   │   ├── 0103_specific_heat_capacity.html
│   │   ├── 0103_specific_heat_capacity2.html
│   │   ├── 0103_thermal_equilibrium.html
│   │   ├── 0105_gas_laws.html
│   │   └── 0105_distribution_gas_speed.html
│   ├── 02/                    # Unit 2: Force and Motion
│   │   ├── 0202_freefall.html
│   │   └── 0210_cavendish_experiment.html
│   ├── 03a/                   # Unit 3a: Optics (Reflection & Refraction)
│   │   ├── 0301_how_we_see.html
│   │   ├── 0301_laws_of_reflection.html
│   │   ├── 0301_plane_mirror_ray_diagram.html
│   │   ├── 0301_plane_mirror_ray_diagram2.html
│   │   ├── 0302_analogy_refraction.html
│   │   ├── 0302_snells_law.html
│   │   ├── 0302_dispersion.html
│   │   └── 0303_lenses.html
│   ├── 03b/                   # Unit 3b: Wave Interference & Diffraction
│   │   ├── 0306_diffraction_grating.html
│   │   ├── 0306_youngs_double_slit.html
│   │   └── 0307_signal_generator.html
│   ├── 04b/                   # Unit 4b: Electromagnetism
│   │   ├── 0405_magnetic_field_lines.html
│   │   └── 0406_mass_spectrometer.html
│   ├── 05/                    # Unit 5: Radioactivity
│   │   └── 0501_geiger_muller_counter.html
│   ├── e01/                   # Elective 1: Astronomy
│   │   └── e0104_fraunhofer_lines.html
│   ├── e02/                   # Elective 2: Atomic Physics
│   │   ├── e0201_photoelectric_effect.html
│   │   ├── e0201_rutherford_experiment.html
│   │   ├── e0202_electron_transition.html
│   │   └── e0202_emission_line_spectra.html
│   └── e03/                   # Elective 3: Energy & Use of Energy
│       ├── e0301_air-conditioner.html
│       ├── e0303_thermal_conductivity.html
│       ├── e0303_ottv.html
│       ├── e0303_cars.html
│       ├── e0304_wind_power.html
│       └── e0304_photovoltaic_plate.html
│
├── chemistry/                 # Chemistry simulations
│   ├── atomic_structure.html
│   ├── ram.html
│   ├── electrolysis.html
│   ├── orbital.html
│   └── firework_display.html
│
├── biology/                   # Biology simulations
│   ├── osmosis.html
│   ├── cell_membrane_permeability.html
│   └── absorption_ileum.html
│
├── science/                   # Integrated Science simulations
│   ├── structure_of_cell.html
│   ├── cell_division.html
│   ├── dna.html
│   ├── microecosystem.html
│   ├── belljar_model.html
│   ├── electric_circuit.html
│   └── algorithm.html
│
├── astronomy/                 # Astronomy simulations
│   ├── hr_diagram.html
│   ├── analemma.html
│   └── solar_system_model.html
│
├── s4_physics/                # S4 Physics curriculum simulations
│   ├── 4A02 - Aero.html
│   ├── 4A02 - AstroLab 2.4.html
│   ├── 4A02 - G-force.html
│   ├── 4A02 - Radiation.html
│   ├── 4A02 - Solaris II.html
│   ├── 4A03 - Heat Transfer.html
│   ├── 4A06 - gravity.html
│   ├── 4A09 - Thin Lens.html
│   ├── 4A14 - Thin Lens.html
│   ├── 4A21 - Thin Lens.html
│   ├── 4A25 - Thin Lens.html
│   └── 4A27 - Thin Lens.html
│
├── other/                     # Miscellaneous simulations
│   ├── 3Dmagneticfield.html
│   ├── chain_rx.html
│   ├── circuit.html
│   ├── circular.html
│   ├── Coefficient of Static Friction.html
│   ├── Electrostatic Induction Sims v3 - Good (with minor bugs).html
│   ├── Friction in riding bicycle V9 - 20251209.html
│   ├── gas.html
│   ├── inclined plane simulation.html
│   ├── Kinetic theory.html
│   ├── moment.html
│   ├── projectile_sim.html
│   ├── refraction.html
│   ├── Sound interference - 20251120.html
│   ├── Stellar life.html
│   ├── vertical motion with parachute.html
│   └── Wave interference.html
│
├── geography/                 # Geography simulations
│   └── BL_Succession.html
│
├── music/                     # Music simulations
│   └── drum-set.html
│
└── travel/                    # Travel-related content
    ├── spain.html
    └── taipei.html
```

---

## Architecture Patterns

### 1. Standalone HTML Files

Each simulation is a **self-contained HTML file** with:
- Embedded CSS (Tailwind CDN + custom styles)
- Embedded JavaScript (inline or `<script>` tags)
- No external file dependencies (except CDN resources)
- No build process required

**Advantages**:
- Easy to deploy (just upload HTML files)
- No compilation errors
- Fast development iteration
- Works directly in browser

**Trade-offs**:
- Code duplication across files
- No shared component library
- Manual dependency management

### 2. CDN-Based Dependencies

All external libraries are loaded via CDN:
- **Pros**: No build step, easy updates, fast global delivery
- **Cons**: Requires internet connection, potential version conflicts

### 3. Responsive Design Pattern

- **Mobile-first**: Tailwind's responsive utilities (`sm:`, `md:`, `lg:`)
- **Fixed header**: Navigation stays visible on scroll
- **Sidebar navigation**: Collapsible on mobile, fixed on desktop
- **Card-based layouts**: Grid system for simulation listings

### 4. Language Support

- **Bilingual support**: Chinese (Traditional) and English
- **Data attributes**: `data-zh` and `data-en` for text switching
- **JavaScript toggle**: `toggleLang()` function updates UI text
- **MathJax**: Renders equations in both languages

### 5. 3D Visualization Pattern

**Three.js Integration**:
```javascript
// Common pattern across 3D simulations
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(45, width/height, 0.1, 100);
const renderer = new THREE.WebGLRenderer({ antialias: true });
const controls = new THREE.OrbitControls(camera, renderer.domElement);

function animate() {
    requestAnimationFrame(animate);
    updatePhysics();
    controls.update();
    renderer.render(scene, camera);
}
```

### 6. Real-time Data Visualization

**Chart.js Integration**:
- Dynamic chart updates based on user input
- Multiple datasets (theoretical vs. current values)
- Responsive canvas sizing
- Custom styling with Tailwind colors

---

## File Organization Principles

### Naming Conventions

1. **Physics files**: `{unit}{topic}_{description}.html`
   - Example: `0105_gas_laws.html` = Unit 1, Topic 5, Gas Laws

2. **Elective files**: `e{number}/{e}{unit}{topic}_{description}.html`
   - Example: `e02/e0201_photoelectric_effect.html` = Elective 2, Unit 2, Topic 1

3. **Other subjects**: `{subject}/{description}.html`
   - Example: `biology/osmosis.html`

### Directory Structure

- **Subject-based**: Each major subject has its own directory
- **Unit-based**: Physics organized by curriculum units
- **Flat structure**: Most directories contain HTML files directly (no subdirectories except physics)

---

## Dependencies

### Required CDN Resources

| Library | Version | Purpose | Loaded In |
|---------|---------|---------|-----------|
| Tailwind CSS | Latest (CDN) | Styling | All pages |
| Three.js | r128, 0.154.0, 0.160.0 | 3D graphics | 3D simulations |
| Chart.js | Latest | Data visualization | Simulations with graphs |
| MathJax | 3.x | Math rendering | Simulations with equations |
| React | 18 (standalone) | UI framework | Advanced simulations |
| Babel Standalone | Latest | JSX transpilation | React-based simulations |

### Browser Compatibility

- **Modern browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **ES6+ features**: Arrow functions, template literals, destructuring
- **WebGL**: Required for Three.js simulations
- **Canvas API**: Required for Chart.js and custom visualizations

---

## Deployment

### GitHub Pages

1. **Repository**: `qos-lkb.github.io`
2. **Branch**: `main` (or `master`)
3. **Build**: None required (static files)
4. **URL**: `https://qos-lkb.github.io`

### Deployment Process

1. Commit changes to repository
2. Push to main branch
3. GitHub Pages automatically deploys
4. Changes live within minutes

### Custom Domain (Optional)

- Configured via GitHub Pages settings
- CNAME file in root directory
- DNS records point to GitHub Pages

---

## Development Guidelines

### Adding a New Simulation

1. **Create HTML file** in appropriate directory
2. **Include CDN dependencies** in `<head>`:
   ```html
   <script src="https://cdn.tailwindcss.com"></script>
   <!-- Add other dependencies as needed -->
   ```
3. **Structure**:
   - Header with title
   - Main content area
   - Controls/inputs (if interactive)
   - Visualization canvas/container
   - Footer (optional)
4. **Link from index.html** or `index_new.html`
5. **Test** in multiple browsers
6. **Commit and push**

### Code Style

- **Indentation**: 4 spaces (or tabs, be consistent)
- **Naming**: camelCase for JavaScript, kebab-case for files
- **Comments**: Explain complex physics calculations
- **Accessibility**: Use semantic HTML, ARIA labels where needed

### Performance Considerations

- **Lazy loading**: Load heavy libraries only when needed
- **Async scripts**: Use `async` or `defer` for non-critical scripts
- **CDN caching**: Leverage browser caching for CDN resources
- **Canvas optimization**: Limit particle counts, use instanced rendering

---

## Future Considerations

### Potential Improvements

1. **Build System**
   - Consider using a static site generator (Jekyll, Eleventy)
   - Bundle optimization
   - Code splitting

2. **Component Library**
   - Extract common UI patterns
   - Shared JavaScript utilities
   - Reusable simulation templates

3. **State Management**
   - URL parameters for sharing specific simulation states
   - LocalStorage for user preferences
   - Session management

4. **Testing**
   - Unit tests for physics calculations
   - Visual regression testing
   - Cross-browser testing automation

5. **Documentation**
   - Inline code documentation
   - User guides for each simulation
   - Developer contribution guide

6. **Performance**
   - Service Worker for offline support
   - Image optimization
   - Lazy loading for simulations

7. **Accessibility**
   - Screen reader support
   - Keyboard navigation
   - High contrast mode
   - WCAG 2.1 compliance

8. **Internationalization**
   - Expand language support
   - RTL language support
   - Localized number formats

---

## Maintenance

### Regular Tasks

- **Update dependencies**: Check for security updates in CDN libraries
- **Browser testing**: Verify compatibility with new browser versions
- **Performance monitoring**: Check page load times
- **Content updates**: Add new simulations, update existing ones
- **Link checking**: Ensure all internal links work

### Version Control

- **Git workflow**: Feature branches for new simulations
- **Commit messages**: Descriptive, reference issue numbers
- **Tags**: Version tags for major releases

---

## Contact & Resources

- **Repository**: `https://github.com/qos-lkb/qos-lkb.github.io`
- **Live Site**: `https://qos-lkb.github.io`
- **Alternative Links**: See `link.txt` for additional resources

---

**Last Updated**: 2025-01-XX  
**Maintained by**: Mr. B. Leung

