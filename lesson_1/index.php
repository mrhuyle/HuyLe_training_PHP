<!DOCTYPE html>
<html>

<head>
  <title>Upload Data</title>
</head>

<body>
  <form action="upload.php" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload" />
    <input type="submit" value="Upload File" name="submit" />
  </form>

  <form action="export_select_into_outfile.php" method="post">
    Search Address: <input type="text" name="searchAddress" />
    Sort Birthdate:
    <select name="sortOrder">
      <option value="ASC">Ascending</option>
      <option value="DESC">Descending</option>
    </select>
    <input type="submit" value="Download CSV" />
  </form>
</body>

</html>