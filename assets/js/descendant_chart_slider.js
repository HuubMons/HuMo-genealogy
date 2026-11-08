$(function () {
    const sliderElement = $("#slider");

    if (!sliderElement.length) {
        return;
    }

    const slider = sliderElement[0];
    const config = slider.dataset;

    sliderElement.slider({
        value: Number(config.sliderValue),
        min: 0,
        max: 9,
        step: 1,
        slide: function (event, ui) {
            $("#amount").val(ui.value + 1);
        }
    });
    $("#amount").val(sliderElement.slider("value") + 1);

    let startPosition = sliderElement.slider("value");
    sliderElement.on("slidestop", function (event, ui) {
        const endPosition = ui.value;
        if (startPosition !== endPosition) {
            const params = new URLSearchParams({
                main_person: config.mainPerson,
                chosensize: String((endPosition + 1) * 5),
                chosengen: config.chosenGenerations,
                direction: config.direction,
                dnachart: config.dna
            });

            if (config.dna !== "none") {
                params.set("bn", config.basePersonName);
                params.set("bs", config.basePersonSexe);
                params.set("bf", config.basePersonFamc);
                params.set("bg", config.basePersonGednr);
            }

            window.location.href = config.urlBase + params.toString();
        }
        startPosition = endPosition;
    });
});
