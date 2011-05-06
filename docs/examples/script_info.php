    <dl style='border: 1px dotted; padding: 5px;'>
      <dt>Current script</dt><dd><?php print basename($_SERVER['SCRIPT_NAME']); ?></dd>
      <dt>session_name():</dt><dd> <?php print session_name(); ?></dd>
      <dt>session_id():</dt><dd> <?php print session_id(); ?></dd>
    </dl>