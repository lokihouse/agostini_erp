<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>

    @assets
    <script lang="javascript" type="module">
        import * as d3 from "https://cdn.jsdelivr.net/npm/d3@7/+esm";

        document.addEventListener("DOMContentLoaded", () => {
            let container = document.getElementById("container");

            let data = [
                { source: 'Departamento A.Máquina 1', target: 'B', type: 'X'},
                { source: 'Departamento A.Máquina 1', target: 'C', type: 'X'},
                { source: 'B', target: 'D', type: 'Y'},
                { source: 'C', target: 'D', type: 'Y'},
            ]

            let types = Array.from(new Set(data.map(d => d.type)));
            let nodes = Array.from(new Set(data.flatMap(l => [l.source, l.target])), id => ({id}));
            let links = data.map(d => Object.create(d));

            const color = d3.scaleOrdinal(types, d3.schemeCategory10);

            const width = container.clientWidth;
            const height = 200;

            const simulation = d3.forceSimulation(nodes)
                .force("link", d3.forceLink(links).id(d => d.id))
                .force("charge", d3.forceManyBody().strength(-400))
                .force("x", d3.forceX())
                .force("y", d3.forceY());

            const svg = d3.create("svg")
                .attr("viewBox", [-width / 2, -height / 2, width, height])
                .attr("width", width)
                .attr("height", height)
                .attr("style", "max-width: 100%; height: auto; background-color: #efefef; font: 12px sans-serif;");

            svg.append("defs")
                .selectAll("marker")
                .data(types)
                .join("marker")
                .attr("id", d => `arrow-${d}`)
                .attr("viewBox", "0 -5 10 10")
                .attr("refX", 15)
                .attr("refY", -0.5)
                .attr("markerWidth", 6)
                .attr("markerHeight", 6)
                .attr("orient", "auto")
                .append("path")
                .attr("fill", color)
                .attr("d", "M0,-5L10,0L0,5");

            const node = svg.append("g")
                .attr("fill", "currentColor")
                .attr("stroke-linecap", "round")
                .attr("stroke-linejoin", "round")
                .selectAll("g")
                .data(nodes)
                .join("g");

            const link = svg.append("g")
                .attr("fill", "none")
                .attr("stroke-width", 1.5)
                .selectAll("path")
                .data(links)
                .join("path")
                .attr("stroke", d => color(d.type))
                .attr("marker-end", d => `url(${new URL(`#arrow-${d.type}`, location)})`);

            node.append("circle")
                .attr("stroke", "white")
                .attr("stroke-width", 1.5)
                .attr("r", 4);

            node.append("text")
                .attr("x", 8)
                .attr("y", "0.31em")
                .text(d => d.id)
                .clone(true).lower()
                .attr("fill", "none")
                .attr("stroke", "white")
                .attr("stroke-width", 3);

            simulation.on("tick", () => {
                link.attr("d", (d) => {
                    const r = Math.hypot(d.target.x - d.source.x, d.target.y - d.source.y);
                    return `M${d.source.x},${d.source.y} A${r},${r} 0 0,1 ${d.target.x},${d.target.y}`;
                });
                node.attr("transform", d => `translate(${d.x},${d.y})`);
            });



            container.append(svg.node());
        });
    </script>
    @endassets

    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <div class="grid grid-cols-10">
            <div class="col-span-2">
                asdf
            </div>
            <div class="col-span-6" id="container"></div>
            <div class="col-span-2">
                asdf
            </div>
        </div>
    </div>
</x-dynamic-component>
