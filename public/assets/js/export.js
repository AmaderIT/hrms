function exportXcel(fileName = "export", tblId = "dataTable", hiddenColumn = "Action") {

    var workbook = XLSX.utils.book_new();

    var worksheet_data = document.getElementById(tblId);
    var worksheet = XLSX.utils.table_to_sheet(worksheet_data);

    $.each(worksheet, function (x, y) {

        if (y.v == hiddenColumn) {
            delete worksheet[x]
        }
    })

    workbook.SheetNames.push("sheet1");
    workbook.Sheets["sheet1"] = worksheet;
    const d = new Date();
    XLSX.writeFile(workbook, fileName + "-" + d.getTime() + ".xlsx");
}


