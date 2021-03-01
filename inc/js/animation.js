/********************* JS avis ***********/
if (document.getElementsByClassName('avisContainer').length !== 0) {

    let avis = document.getElementsByClassName('avisContainer');
    function animationFull() {
        let heightCount = 0;
        let heightFull = 150;
        let id = setInterval(frame, 5);

        function frame() {
            if (avis[0].offsetHeight == heightFull) {
                avis[0].style.height = 100 + '%';

                clearInterval(id);
            } else {
                if (avis[0].offsetHeight == 100) {
                    document.getElementById('avis').style.display = "block";
                }
                heightCount++;
                avis[0].style.height = heightCount + 'px';
            }

        }

    }
    function animationBlank() {
        let heightCount = 128;
        let heightBlank = 0;
        let id = setInterval(frame, 5);

        function frame() {
            if (avis[0].offsetHeight == heightBlank) {
                clearInterval(id);
            } else {
                heightCount--;
                avis[0].style.height = heightCount + 'px';
                if (avis[0].offsetHeight == 100) {
                    document.getElementById('avis').style.display = "none";
                }
            }

        }

    }
    document.getElementById('avisBtn').addEventListener('click', function () {
        if (avis[0].offsetHeight == 0) {
            animationFull();
        } else {
            animationBlank();
        }
    })
}