let A = true;

function changeStyle() {
    const styleA = "resources/css/table-style-A.css";
    const styleB = "resources/css/table-style-B.css";
    const link = document.getElementById("table-css-link");
    if (A) {
        link.href = styleB;
        A = false;
    } else {
        link.href = styleA;
        A = true;
    }
}