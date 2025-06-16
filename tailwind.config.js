import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: "#ffc02a",
            },
            backgroundImage: {
                dotted: "radial-gradient(rgba(0, 0, 0, 0.1) 1px, transparent 1px);",
            },
            backgroundSize: {
                dotted: "10px 10px",
            },
        },
    },

    plugins: [forms],
};
