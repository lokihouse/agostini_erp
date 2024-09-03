@props([
    'navigation',
])

<ul
    wire:ignore
    {{ $attributes->class(['fi-page-sub-navigation-sidebar hidden flex-col gap-y-4 md:flex']) }}
    style="width: 224px"
>
    @foreach ($navigation as $navigationGroup)
        <x-filament-panels::sidebar.group
            :active="$navigationGroup->isActive()"
            :collapsible="$navigationGroup->isCollapsible()"
            :icon="$navigationGroup->getIcon()"
            :items="$navigationGroup->getItems()"
            :label="$navigationGroup->getLabel()"
            :sidebar-collapsible="false"
            sub-navigation
            :attributes="\Filament\Support\prepare_inherited_attributes($navigationGroup->getExtraSidebarAttributeBag())"
        />
    @endforeach
</ul>
