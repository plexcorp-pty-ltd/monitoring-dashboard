<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deeply Pastoral Marmot</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-black">
    <section class="py-20 pb-32">

    <div class="relative max-w-6xl px-10 mx-auto">
        <div class="flex flex-col items-start justify-start mb-12">
            <h2 class="inline-block mb-2 mr-5 text-4xl font-extrabold tracking-tight text-white">Server resource dashboard</h2>
            <p class="text-xl text-gray-400">Realtime server stats on CPU, RAM, Disk usage and so forth 🔮</p>
        </div>

        <div class="grid grid-cols-1 gap-10 mt-10 md:grid-cols-1 xl:grid-cols-1">

            <!-- Member 1 -->
            <div class="relative rounded-lg p-0.5 overflow-hidden bg-transparent shadow-sm hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">

                <div class="relative z-10 flex items-center w-full h-full px-6 py-5 bg-black rounded-lg">

         
               <img src="https://quickchart.io/chart?c={type:'bar',data:{labels:['Q1','Q2','Q3','Q4'], datasets:[{label:'Users',data:[50,60,70,180]},{label:'Revenue',data:[100,200,300,400]}]}}" />
             
         
                </div>
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-yellow-400 via-purple-400 to-pink-500"></div>

            </div>

        </div>
    </div>

</section>

</body>
</html>
