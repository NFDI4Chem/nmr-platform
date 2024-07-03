import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Company/**/*.php',
        './resources/views/filament/company/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
