import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Company/**/*.php',
        './resources/views/filament/company/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/archilex/filament-filter-sets/**/*.php',
        './vendor/ralphjsmit/laravel-filament-media-library/resources/**/*.blade.php',
        './vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',
    ],
}
