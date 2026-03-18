import sys
import os

try:
    from PIL import Image
except ImportError:
    import subprocess
    subprocess.check_call([sys.executable, "-m", "pip", "install", "Pillow"])
    from PIL import Image

def generate_favicons(input_path, output_dir):
    try:
        img = Image.open(input_path)
        img = img.convert("RGBA")
        
        # Ensure output directory exists
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)
            
        # Common sizes
        sizes = {
            "favicon-16x16.png": (16, 16),
            "favicon-32x32.png": (32, 32),
            "apple-touch-icon.png": (180, 180),
            "android-chrome-192x192.png": (192, 192),
            "android-chrome-512x512.png": (512, 512),
        }
        
        # Generate PNGs
        for filename, size in sizes.items():
            resized = img.resize(size, Image.Resampling.LANCZOS)
            out_path = os.path.join(output_dir, filename)
            resized.save(out_path, format="PNG")
            print(f"Generated {out_path}")
            
        # Generate ICO (combines 16, 32, 48)
        ico_path = os.path.join(output_dir, "favicon.ico")
        icon_sizes = [(16, 16), (32, 32), (48, 48), (256, 256)]
        img.save(ico_path, format="ICO", sizes=icon_sizes)
        print(f"Generated {ico_path}")
        
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python gen_icons.py <input_image> <output_dir>")
        sys.exit(1)
    generate_favicons(sys.argv[1], sys.argv[2])
