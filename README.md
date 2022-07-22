Horizontal form (bootstrap)
=======================
This function requires the TwbsHelper module to be added.
See neilime/twbs-helper-module

Add to the view where the form is used
```
$this->formCollection()->setElementHelper($this->formRowHorizontal());
$form->prepare();
echo $this->form()->openTag($form);
echo $this->formCollection($form);
echo $this->form()->closeTag();
```
