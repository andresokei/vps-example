<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-sm text-white tracking-wide focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150']) }}
        style="background: linear-gradient(135deg, #2937f0, #9f1ae2);"
        onmouseover="this.style.opacity='0.9'"
        onmouseout="this.style.opacity='1'">
    {{ $slot }}
</button>
