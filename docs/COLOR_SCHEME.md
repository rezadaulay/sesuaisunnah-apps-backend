# Color Scheme Documentation

## Overview

Sesuai Sunnah Apps menggunakan skema warna yang konsisten dan terstandarisasi untuk frontend dan backend. Skema warna ini dirancang untuk memberikan pengalaman visual yang harmonis dan mudah diakses.

## Color Palette

### Primary Colors
- **Main**: `#005555` - Warna utama brand, digunakan untuk tombol, link, dan header
- **Light**: `#67cbcb` - Varian terang untuk hover states dan background
- **Dark**: `#003f3f` - Varian gelap untuk text dan emphasis

### Secondary Colors
- **Main**: `#FFC700` - Warna aksen, digunakan untuk highlight dan call-to-action
- **Light**: `#ffd767` - Varian terang untuk background dan border
- **Dark**: `#cc9f00` - Varian gelap untuk text dan emphasis

### Accent Colors
- **Main**: `#0ea5e9` - Warna informasi, link, dan elemen interaktif
- **Light**: `#7dd3fc` - Varian terang
- **Dark**: `#0369a1` - Varian gelap

### Semantic Colors
- **Success**: `#22c55e` - Status sukses, konfirmasi
- **Warning**: `#f59e0b` - Status peringatan, alert
- **Error**: `#ef4444` - Status error, destructive actions

### Neutral Colors
- **50**: `#fafafa` - Background sangat terang
- **100**: `#f5f5f5` - Background terang
- **200**: `#e5e5e5` - Border dan divider
- **300**: `#d4d4d4` - Border dan icon
- **400**: `#a3a3a3` - Text secondary
- **500**: `#737373` - Text secondary
- **600**: `#525252` - Text secondary
- **700**: `#404040` - Text primary
- **800**: `#262626` - Text primary
- **900**: `#171717` - Text primary

## Usage Guidelines

### Frontend (Next.js + Tailwind CSS)

#### Tailwind Configuration
```typescript
// tailwind.config.ts
theme: {
  extend: {
    colors: {
      primary: {
        50: '#f0f9f9',
        100: '#d9f2f2',
        200: '#b3e5e5',
        300: '#8dd8d8',
        400: '#67cbcb',
        500: '#005555', // Main primary color
        600: '#004a4a',
        700: '#003f3f',
        800: '#003434',
        900: '#002929',
        950: '#001e1e',
      },
      secondary: {
        50: '#fffbf0',
        100: '#fff5d9',
        200: '#ffebb3',
        300: '#ffe18d',
        400: '#ffd767',
        500: '#FFC700', // Main secondary color
        600: '#e6b300',
        700: '#cc9f00',
        800: '#b38b00',
        900: '#997700',
        950: '#806300',
      },
      // ... other colors
    }
  }
}
```

#### CSS Variables
```css
:root {
  --color-primary: #005555;
  --color-primary-light: #67cbcb;
  --color-primary-dark: #003f3f;
  
  --color-secondary: #FFC700;
  --color-secondary-light: #ffd767;
  --color-secondary-dark: #cc9f00;
  
  --color-accent: #0ea5e9;
  --color-success: #22c55e;
  --color-warning: #f59e0b;
  --color-error: #ef4444;
}
```

#### Component Usage
```tsx
// Buttons
<button className="btn btn-primary">Primary Button</button>
<button className="btn btn-secondary">Secondary Button</button>
<button className="btn btn-accent">Accent Button</button>

// Cards
<div className="card bg-white border-neutral-200">
  <h3 className="text-primary-600">Card Title</h3>
</div>

// Forms
<input className="form-input focus:ring-primary-500 focus:border-primary-500" />
```

### Backend (Laravel)

#### Configuration File
```php
// config/colors.php
return [
    'primary' => [
        'main' => '#005555',
        'light' => '#67cbcb',
        'dark' => '#003f3f',
        // ... shades
    ],
    'secondary' => [
        'main' => '#FFC700',
        'light' => '#ffd767',
        'dark' => '#cc9f00',
        // ... shades
    ],
    // ... other colors
];
```

#### Helper Usage
```php
use App\Helpers\ColorHelper;

// Get specific colors
$primaryColor = ColorHelper::primary();
$secondaryColor = ColorHelper::secondary('light');

// Get color palette
$palette = ColorHelper::getPalette();

// Generate variations
$lightened = ColorHelper::lighten('#005555', 20);
$darkened = ColorHelper::darken('#005555', 20);

// Check brightness
$brightness = ColorHelper::getBrightness('#005555');
$contrastText = ColorHelper::getContrastTextColor('#005555');
```

#### API Endpoints
```bash
# Get complete color scheme
GET /api/colors

# Get specific color
GET /api/colors/get?key=primary.main

# Get CSS variables
GET /api/colors/css-variables

# Get primary colors
GET /api/colors/primary

# Get secondary colors
GET /api/colors/secondary

# Get semantic colors
GET /api/colors/semantic

# Generate color variations
POST /api/colors/variations
{
    "color": "#005555",
    "percentage": 20
}
```

## Design Principles

### 1. Consistency
- Gunakan warna yang sama untuk elemen yang sama di seluruh aplikasi
- Konsisten dalam penggunaan semantic colors (success, warning, error)

### 2. Accessibility
- Pastikan contrast ratio memenuhi standar WCAG 2.1 AA
- Gunakan `ColorHelper::getContrastTextColor()` untuk text yang kontras

### 3. Hierarchy
- Primary color untuk elemen utama dan brand
- Secondary color untuk aksen dan call-to-action
- Neutral colors untuk text dan background

### 4. States
- Hover states menggunakan warna yang lebih gelap atau terang
- Focus states menggunakan ring dengan warna primary
- Disabled states menggunakan neutral colors

## Implementation Examples

### Button Component
```tsx
interface ButtonProps {
  variant: 'primary' | 'secondary' | 'accent' | 'outline';
  size: 'sm' | 'md' | 'lg';
  children: React.ReactNode;
  disabled?: boolean;
}

const Button: React.FC<ButtonProps> = ({ variant, size, children, disabled }) => {
  const baseClasses = "btn font-semibold rounded-lg transition-all duration-200";
  
  const variantClasses = {
    primary: "bg-primary-500 hover:bg-primary-600 text-white border-primary-500",
    secondary: "bg-secondary-500 hover:bg-secondary-600 text-neutral-900 border-secondary-500",
    accent: "bg-accent-500 hover:bg-accent-600 text-white border-accent-500",
    outline: "border-neutral-300 text-neutral-700 hover:bg-neutral-100 hover:border-neutral-400"
  };
  
  const sizeClasses = {
    sm: "px-3 py-2 text-sm",
    md: "px-4 py-2 text-base",
    lg: "px-6 py-3 text-lg"
  };
  
  const disabledClasses = disabled ? "opacity-50 cursor-not-allowed" : "";
  
  return (
    <button 
      className={`${baseClasses} ${variantClasses[variant]} ${sizeClasses[size]} ${disabledClasses}`}
      disabled={disabled}
    >
      {children}
    </button>
  );
};
```

### Card Component
```tsx
interface CardProps {
  children: React.ReactNode;
  variant?: 'default' | 'primary' | 'secondary';
  hover?: boolean;
}

const Card: React.FC<CardProps> = ({ children, variant = 'default', hover = false }) => {
  const baseClasses = "card rounded-lg shadow-sm border transition-all duration-300";
  
  const variantClasses = {
    default: "bg-white border-neutral-200",
    primary: "bg-primary-50 border-primary-200",
    secondary: "bg-secondary-50 border-secondary-200"
  };
  
  const hoverClasses = hover ? "hover:shadow-medium hover:border-primary-200" : "";
  
  return (
    <div className={`${baseClasses} ${variantClasses[variant]} ${hoverClasses}`}>
      {children}
    </div>
  );
};
```

## Testing

### Frontend Testing
```typescript
// Test color utility functions
describe('Color Utilities', () => {
  it('should return correct primary color', () => {
    expect(getPrimaryColor()).toBe('#005555');
  });
  
  it('should return correct secondary color', () => {
    expect(getSecondaryColor()).toBe('#FFC700');
  });
});
```

### Backend Testing
```php
// Test ColorHelper
class ColorHelperTest extends TestCase
{
    public function test_primary_color()
    {
        $this->assertEquals('#005555', ColorHelper::primary());
    }
    
    public function test_secondary_color()
    {
        $this->assertEquals('#FFC700', ColorHelper::secondary());
    }
    
    public function test_color_brightness()
    {
        $this->assertEquals('dark', ColorHelper::getBrightness('#005555'));
        $this->assertEquals('light', ColorHelper::getBrightness('#FFC700'));
    }
}
```

## Maintenance

### Adding New Colors
1. Update `config/colors.php` dengan warna baru
2. Update `ColorHelper.php` dengan method baru jika diperlukan
3. Update `tailwind.config.ts` dengan warna baru
4. Update CSS variables di `globals.css`
5. Update dokumentasi ini

### Color Updates
1. Pastikan semua file konfigurasi diupdate
2. Test kontras dan accessibility
3. Update komponen yang menggunakan warna lama
4. Update dokumentasi

## Resources

- [Tailwind CSS Color Palette](https://tailwindcss.com/docs/customizing-colors)
- [WCAG 2.1 Color Contrast](https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html)
- [Color Theory for Designers](https://www.smashingmagazine.com/2010/02/color-theory-for-designers-part-1-the-meaning-of-color/)
