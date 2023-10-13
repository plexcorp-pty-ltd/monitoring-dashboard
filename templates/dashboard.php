<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deeply Pastoral Marmot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="
    https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js
    "></script>
</head>
<body class="bg-black">
    <section class="py-20 pb-32">

    <div class="relative max-w-8xl px-10 mx-auto">
        <div class="flex flex-col items-start justify-start mb-12">
            <h2 class="inline-block mb-2 mr-5 text-4xl font-extrabold tracking-tight text-white">System monitor stats [<?php if($host=='all'):?> All hosts <?php else:?> <?php print $host;?><?php endif;?> ]</h2>
           <select id="changeHost" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-1/5 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">">
                <option>Change host</option>
                <?php foreach($hosts as $h):?>
                        <option value="<?php echo $h['hostname'];?>"><?php echo $h['hostname'];?></option>
                <?php endforeach;?>
                <option value="pokemon">Pokemon</option>
            </select>
        </div>

        <div class="grid gap-10 mt-10 grid-cols-2">

            <div class="relative rounded-lg p-0.5 overflow-hidden bg-transparent shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">

                <div class="relative z-10 flex items-center w-full h-full px-6 py-5 bg-black rounded-lg bg-blue-950">
                    <canvas id="cpu"></canvas>
         
                </div>
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-yellow-400 via-purple-400 to-pink-500"></div>

            </div>

            <div class="relative rounded-lg p-0.5 overflow-hidden bg-transparent shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">

                <div class="relative z-10 flex items-center w-full h-full px-6 py-5 bg-black rounded-lg bg-blue-950">
                    <canvas id="memory"></canvas>
         
                </div>
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-yellow-400 via-purple-400 to-pink-500"></div>

            </div>

            <div class="relative rounded-lg p-0.5 overflow-hidden bg-transparent shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">

                <div class="relative z-10 flex items-center w-full h-full px-6 py-5 bg-black rounded-lg bg-blue-950">
                    <canvas id="diskspace"></canvas>
         
                </div>
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-yellow-400 via-purple-400 to-pink-500"></div>

            </div>


            <div class="relative rounded-lg p-0.5 overflow-hidden bg-transparent shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">

                <div class="relative z-10 flex items-center w-full h-full px-6 py-5 bg-black rounded-lg bg-blue-950">
                    <canvas id="number_running_processes"></canvas>
         
                </div>
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-yellow-400 via-purple-400 to-pink-500"></div>

            </div>

        </div>
    </div>

</section>

<script>
        
    Chart.defaults.color = '#fff';
    async function drawLineChart(chartType, label) {
        const chartObjName = chartType + "_chart";
        console.log(chartObjName)
        window[chartObjName] = null;
        const ctx = document.getElementById(chartType);
        response = await fetch("/?action=api&route=stats&stat_type=" + chartType + "&stat_host=<?php print $host;?>");
        if (response.status == 200) {
            response = await response.json();
            window[chartObjName] = new Chart(ctx, {
                type: 'line',
        
                data: {
                  labels: response.map((r) => r.dt_time),
                  datasets: [{
                    label: label,
                    data: response.map((r) =>  parseFloat(r.stat_numerical)),
                    borderWidth: 1
                  }]
                }
            });

            // Update loop - check for new logs every few minutes.
            setInterval(function() {
                fetch("/?action=api&route=stats&stat_type=" + chartType + "&stat_host=<?php print $host;?>").then((response) => {
                    if (response.status == 200) {
                        return response.json();
                    }
                    
                    return null;
                }).then((chartdata) => {
                    if (chartdata != null) {
                        window[chartObjName].data.labels = chartdata.map(l =>l.dt_time);
                        window[chartObjName].data.datasets[0].data = chartdata.map(d =>  parseFloat(d.stat_numerical));
                        window[chartObjName].update();
                    }
                });
              }, 10000);
            // Update loop - end
        }

    }

    

    (() => {
        drawLineChart("cpu", '% CPU Utilization');
        drawLineChart("memory", '% Memory Utilization');
        drawLineChart("diskspace", '% Disk Space Utilization');
        drawLineChart("number_running_processes", 'No. of Running Processes');
        setInterval(function() {
            window.location.reload();
        }, 100000);
    })();

    document.getElementById('changeHost').addEventListener("change", function(e) {
        window.location = "?stat_host=" + e.target.value;
    });

</script>
</body>
</html>
