<div wire:ignore x-data="vis_network_{{ $id }}" x-init="init()">
    <div class="w-full h-64" id="vis-network-{{ $id }}"></div>
</div>

@assets
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
@endassets

@script
<script>
    Alpine.data("vis_network_{{ $id }}", () => {
        return {
            init() {
                var nodes = @js($nodes);
                var edges = @js($edges);

                var container = document.getElementById('vis-network-{{ $id }}');

                var data = {
                    nodes: nodes,
                    edges: edges
                };
                var options = {
                    interaction: { hover: true },
                    layout: {
                        // hierarchical: {
                        //     direction: "LR",
                        //     sortMethod: "directed",
                        // },
                    },
                    physics: {
                        enabled: true,
                    },
                };
                new vis.Network(container, data, options);
            }
        }
    });
</script>
@endscript
