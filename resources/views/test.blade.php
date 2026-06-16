<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FAQ Accordion</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #e7f3ff;
      margin: 0;
      padding: 20px;
    }

    .accordion {
      max-width: 700px;
      margin: 20px auto;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .accordion-item {
      border-bottom: 1px solid #ddd;
    }

    .accordion-title {
      padding: 20px;
      cursor: pointer;
      background-color: white;
      font-weight: bold;
      transition: background-color 0.2s ease;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .accordion-title:hover {
      background-color: #f0f0f0;
    }

    .accordion-content {
      display: none;
      padding: 0 20px 20px 20px;
      background-color: white;
      font-size: 15px;
      line-height: 1.5;
    }

    .accordion-item.active .accordion-content {
      display: block;
    }

    .accordion-item.active .accordion-title {
      color: #3e64ff;
    }

    .arrow {
      font-size: 16px;
      transition: transform 0.2s ease;
    }

    .accordion-item.active .arrow {
      transform: rotate(180deg); /* Switches arrow direction */
    }
  </style>
</head>
<body>


<div class="modal-body">
                    <form id="utilityForm" action="{{ route('utilities.add') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="site_id" name="site_id">

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <label for="amount">Amount</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="amount" name="amount" type="number" class="form-control no-arrow" min="0" step="0.01"
                                    placeholder="Enter Amount" />
                            </div>
                            @error('amount')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row align-items-center mt-3">
                            <div class="col-lg-2">
                                <label for="image">Image</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="image" name="image" type="file" class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp" />
                            </div>
                            @error('image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row align-items-center mt-3">
                            <div class="col-lg-2">
                                <label for="remarks">Remarks</label>
                            </div>
                            <div class="col-lg-10">
                                <textarea id="remarks" name="remarks" class="form-control" rows="4"></textarea>
                            </div>
                            @error('remarks')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-primary" id="saveButton">Add</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


<script>
    const items = document.querySelectorAll('.accordion-item');

    items.forEach(item => {
      item.querySelector('.accordion-title').addEventListener('click', () => {
        // Collapse all others (optional)
        items.forEach(i => {
          if (i !== item) i.classList.remove('active');
        });

        item.classList.toggle('active');
      });
    });
  </script>

</body>
</html>
