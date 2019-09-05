Feature: Adding price set option for other amount

Scenario: On a Contribution form with an other amount field configured.

When A Price Set is configured with a Price Field with the Input Field Type: "Radio"
AND The "Allow Other Amounts" checkbox is checked
AND "Display Amount?" is not checked
AND There is a price option for this price field with the label "Other Amount"
AND The price field is used for a Contribution Form
THEN On that Contribution Form an Other Amount box will appear in place of the Other Amount option
AND if the other amount box is clicked the Other Amount will be used instead of whatever other price had been selected.
