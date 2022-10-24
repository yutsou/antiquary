class countdown {
    constructor(dueTimeIso, nextDo=null) {
        this.dueTimeIso = dueTimeIso;
        this.nextDo = nextDo;
        this.x = null;
    }

    init() {
        const second = 1000,
            minute = second * 60,
            hour = minute * 60,
            day = hour * 24;

        const dueTime = new Date(this.dueTimeIso).getTime();

        this.x = setInterval(function(){
            const now = new Date().getTime(),
                distance = dueTime - now;
            $(".countdown-days").text(Math.floor(distance / (day)).toString().padStart(2, '0'));
            $(".countdown-hours").text(Math.floor((distance % (day)) / (hour)).toString().padStart(2, '0'));
            $(".countdown-minutes").text(Math.floor((distance % (hour)) / (minute)).toString().padStart(2, '0'));
            $(".countdown-seconds").text(Math.floor((distance % (minute)) / second).toString().padStart(2, '0'));

            //do something later when date is reached
            if (distance <= 1) {
                clearInterval(this.x);
            }
            //seconds
        },0)
    }

    clear() {
        clearInterval(this.x);
    }



}
