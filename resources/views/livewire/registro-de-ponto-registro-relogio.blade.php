@script
<script>
    const calendarDiv = document.getElementById('calendar');
    const clockDiv = document.getElementById('timer');

    function updateClock() {
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = now.toLocaleString('default', { month: 'long' });
        const year = String(now.getFullYear());
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        clockDiv.textContent = `${hours}:${minutes}:${seconds}`;
        calendarDiv.textContent = `${day} de ${month} de ${year}`;
    }

    updateClock();
    setInterval(updateClock, 1000);


</script>
@endscript

<div class="absolute top-14 w-full z-50 flex justify-center">
    <div class="bg-white rounded-xl border shadow-lg m-4 p-4 max-w-md w-full">
        <div class="text-center text-xl font-bold">{{ auth()->user()->nome }}</div>
        <div class="text-center text-md font-thin">Deseja registrar seu ponto?</div>
        <div id="timer" class="text-center text-6xl font-bold pt-4">99:99:99</div>
        <div id="calendar" class="text-center text-md font-thin">01 de janeiro de 1970</div>
    </div>
</div>
