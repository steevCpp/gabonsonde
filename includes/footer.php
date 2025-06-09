<!--FOOTER SECTION -->
    <div id="footer" style="text-align:right;">
        2024 www.gabonsonde.com | gabonsonde@gmail.com | All Right Reserved

    </div>
    <!-- END FOOTER SECTION -->

    <!-- Note: The extra </div> that was here previously has been removed as it was page-specific. -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var dynamicText = document.getElementById('dynamic-text');
            var messages = [
                'GabonSonde',
                'Sondages en ligne',
                'Participez et gagnez',
                'Votre opinion compte'
            ];
            var index = 0;

            function changeText() {
                if (dynamicText) { // Check if element exists
                    dynamicText.textContent = messages[index];
                    index = (index + 1) % messages.length;
                }
            }
            // Ensure dynamic-text element exists before calling setInterval if it's crucial
            if (dynamicText) {
                 setInterval(changeText, 3000); // Change text every 3 seconds
            }
        });
    </script>

    <!-- Existing custom scripts that might rely on old jQuery - potential breakage to be addressed later -->
    <script src="/gabonsonde/assets/js/home_assets_js/custom.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="/gabonsonde/assets/js/home_assets_js/charts.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
