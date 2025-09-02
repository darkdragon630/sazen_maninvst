/* Modern Dashboard CSS - Enhanced Version */

/* CSS Variables for consistent theming */
:root {
  /* Primary Colors */
  --primary-color: #667eea;
  --primary-dark: #5a6fd8;
  --primary-light: #a5b4fc;
  --secondary-color: #f093fb;
  --accent-color: #4facfe;
  
  /* Status Colors */
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --error-color: #ef4444;
  --info-color: #3b82f6;
  
  /* Neutral Colors */
  --background-primary: #0f172a;
  --background-secondary: #1e293b;
  --background-tertiary: #334155;
  --surface-primary: #1e293b;
  --surface-secondary: #334155;
  --surface-hover: #475569;
  
  /* Text Colors */
  --text-primary: #f8fafc;
  --text-secondary: #cbd5e1;
  --text-muted: #94a3b8;
  --text-accent: #a5b4fc;
  
  /* Border & Shadow */
  --border-color: #334155;
  --border-light: #475569;
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  
  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;
  --spacing-2xl: 3rem;
  
  /* Border Radius */
  --radius-sm: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --radius-xl: 1rem;
  
  /* Transitions */
  --transition-fast: 150ms ease-in-out;
  --transition-normal: 250ms ease-in-out;
  --transition-slow: 350ms ease-in-out;
}

/* Global Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html {
  font-size: 16px;
  scroll-behavior: smooth;
}

body {
  font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, var(--background-primary) 0%, var(--background-secondary) 100%);
  color: var(--text-primary);
  line-height: 1.6;
  min-height: 100vh;
  overflow-x: hidden;
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: var(--spacing-xl) var(--spacing-lg);
  min-height: 100vh;
}

/* Investments Section */
.investments-section {
  background: var(--surface-primary);
  border-radius: var(--radius-xl);
  padding: var(--spacing-2xl);
  box-shadow: var(--shadow-xl);
  border: 1px solid var(--border-color);
  position: relative;
  overflow: hidden;
}

.investments-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
  border-radius: var(--radius-xl) var(--radius-xl) 0 0;
}

/* Section Header */
.section-header {
  margin-bottom: var(--spacing-2xl);
  text-align: center;
  position: relative;
}

.header-content {
  position: relative;
  z-index: 1;
}

.section-title {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: var(--spacing-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-md);
}

.section-title i {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-size: 1.8rem;
}

.section-subtitle {
  color: var(--text-secondary);
  font-size: 1.1rem;
  font-weight: 400;
}

/* Current Data Display */
.current-data {
  background: linear-gradient(135deg, var(--surface-secondary), var(--surface-primary));
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
  margin-bottom: var(--spacing-xl);
  box-shadow: var(--shadow-md);
  position: relative;
  overflow: hidden;
}

.current-data::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, var(--info-color), var(--primary-color));
}

.current-data strong {
  color: var(--text-accent);
  font-weight: 600;
}

/* Alert Styles */
.alert {
  padding: var(--spacing-lg);
  border-radius: var(--radius-lg);
  margin-bottom: var(--spacing-xl);
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  font-weight: 500;
  box-shadow: var(--shadow-md);
  border: 1px solid transparent;
}

.alert.error {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.2);
}

.alert.success {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
  color: #86efac;
  border-color: rgba(16, 185, 129, 0.2);
}

/* Form Styles */
.form-container {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xl);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.form-group label {
  font-weight: 600;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: 0.95rem;
}

.form-group label i {
  color: var(--primary-color);
  width: 20px;
  text-align: center;
}

.form-control {
  background: var(--surface-secondary);
  border: 2px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--spacing-md) var(--spacing-lg);
  color: var(--text-primary);
  font-size: 1rem;
  transition: all var(--transition-normal);
  width: 100%;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
  background: var(--surface-primary);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  transform: translateY(-1px);
}

.form-control:hover {
  border-color: var(--border-light);
  background: var(--surface-primary);
}

.form-control::placeholder {
  color: var(--text-muted);
}

/* Form Row */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-lg);
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}

/* Investment Info */
.investment-info {
  background: linear-gradient(135deg, var(--surface-secondary), var(--background-tertiary));
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
  margin: var(--spacing-md) 0;
  opacity: 0;
  transform: translateY(-10px);
  transition: all var(--transition-normal);
  box-shadow: var(--shadow-sm);
}

.investment-info.show {
  opacity: 1;
  transform: translateY(0);
}

.investment-info strong {
  color: var(--text-accent);
  font-weight: 600;
}

/* Profit Types */
.profit-types {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: var(--spacing-md);
  margin-top: var(--spacing-md);
}

.profit-type {
  background: var(--surface-secondary);
  border: 2px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
  text-align: center;
  cursor: pointer;
  transition: all var(--transition-normal);
  position: relative;
  overflow: hidden;
  user-select: none;
}

.profit-type::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
  transition: left var(--transition-slow);
}

.profit-type:hover {
  border-color: var(--primary-color);
  background: var(--surface-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.profit-type:hover::before {
  left: 100%;
}

.profit-type.selected {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(240, 147, 251, 0.1));
  border-color: var(--primary-color);
  color: var(--text-accent);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.profit-type i {
  font-size: 1.5rem;
  color: var(--primary-color);
  margin-bottom: var(--spacing-sm);
  display: block;
}

.profit-type input[type="radio"] {
  display: none;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-md) var(--spacing-xl);
  border: none;
  border-radius: var(--radius-lg);
  font-size: 1rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all var(--transition-normal);
  position: relative;
  overflow: hidden;
  min-width: 140px;
  box-shadow: var(--shadow-md);
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left var(--transition-slow);
}

.btn:hover::before {
  left: 100%;
}

.btn-warning {
  background: linear-gradient(135deg, var(--warning-color), #f97316);
  color: white;
}

.btn-warning:hover {
  background: linear-gradient(135deg, #f97316, var(--warning-color));
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.btn-secondary {
  background: linear-gradient(135deg, var(--surface-secondary), var(--surface-tertiary));
  color: var(--text-primary);
  border: 1px solid var(--border-light);
}

.btn-secondary:hover {
  background: linear-gradient(135deg, var(--surface-tertiary), var(--surface-secondary));
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.btn:active {
  transform: translateY(0);
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: var(--spacing-md);
  justify-content: flex-start;
  flex-wrap: wrap;
  margin-top: var(--spacing-lg);
  padding-top: var(--spacing-lg);
  border-top: 1px solid var(--border-color);
}

/* Textarea */
textarea.form-control {
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

/* Select Dropdown */
select.form-control {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 12px center;
  background-repeat: no-repeat;
  background-size: 16px;
  padding-right: 40px;
  cursor: pointer;
}

select.form-control option {
  background: var(--surface-secondary);
  color: var(--text-primary);
  padding: var(--spacing-sm);
}

/* Input Number */
input[type="number"].form-control::-webkit-outer-spin-button,
input[type="number"].form-control::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"].form-control {
  -moz-appearance: textfield;
}

/* Date Input */
input[type="date"].form-control {
  position: relative;
}

input[type="date"].form-control::-webkit-calendar-picker-indicator {
  background: transparent;
  bottom: 0;
  color: transparent;
  cursor: pointer;
  height: auto;
  left: 0;
  position: absolute;
  right: 0;
  top: 0;
  width: auto;
}

/* Loading Animation */
.loading {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: var(--primary-color);
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    padding: var(--spacing-lg) var(--spacing-md);
  }
  
  .investments-section {
    padding: var(--spacing-xl);
  }
  
  .section-title {
    font-size: 1.75rem;
    flex-direction: column;
    text-align: center;
  }
  
  .profit-types {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .profit-types {
    grid-template-columns: 1fr;
  }
  
  .section-title {
    font-size: 1.5rem;
  }
  
  .investments-section {
    padding: var(--spacing-lg);
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Focus visible for better accessibility */
.btn:focus-visible,
.form-control:focus-visible,
.profit-type:focus-visible {
  outline: 2px solid var(--primary-color);
  outline-offset: 2px;
}

/* Print styles */
@media print {
  body {
    background: white;
    color: black;
  }
  
  .investments-section {
    background: white;
    box-shadow: none;
    border: 1px solid #ccc;
  }
  
  .btn {
    display: none;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  :root {
    --border-color: #ffffff;
    --text-secondary: #ffffff;
    --text-muted: #cccccc;
  }
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: var(--background-secondary);
}

::-webkit-scrollbar-thumb {
  background: var(--surface-tertiary);
  border-radius: var(--radius-md);
}

::-webkit-scrollbar-thumb:hover {
  background: var(--border-light);
}
